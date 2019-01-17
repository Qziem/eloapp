<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;
use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\UserRepository;
use Slim\Http\Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class UsersCtrl
{
    /** @var EntityManager */
    private $em;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        EntityManager $em,
        UserRepository $userRepository
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    public function getUsers(Response $response): Response
    {
        $userEntityList = $this->userRepository->findBy(
            ['deleted' => 0],
            ['rating' => 'DESC', 'code' => 'ASC']
        );

        $userArrayList = array_map(function (User $user) {
            $userArray = $this->userToArray($user);
            $userArray = $this->calculateTrendRatingDiff($userArray);
            return $this->removeGameListsFromUserArray($userArray);
        }, $userEntityList);

        return $response->withJson($userArrayList);
    }

    private function userToArray(User $user): array
    {
        return [
            'userNid' => $user->getUserNid(),
            'code' => $user->getCode(),
            'name' => $user->getName(),
            'rating' => $user->getRating(),
            'team' => $user->getTeam(),
            'wonGameList' => $user->getLastWonGameList(),
            'lostGameList' => $user->getLastLostGameList(),
        ];
    }

    private function calculateTrendRatingDiff(array $userArray): array
    {
        $winGamesSumRating = $this->sumRatingDiffs($userArray['wonGameList']);
        $looseGamesSumRating = $this->sumRatingDiffs($userArray['lostGameList']);

        $trendRatingDiff = $winGamesSumRating - $looseGamesSumRating;
        $userArray['trendRatingDiff'] = $trendRatingDiff;

        return $userArray;
    }

    private function sumRatingDiffs(Collection $gameList): int
    {
        return array_reduce($gameList->toArray(), function (int $acc, Game $game) {
            return $acc + $game->getRatingDiff();
        }, 0);
    }

    private function removeGameListsFromUserArray(array $userArray): array
    {
        unset($userArray['wonGameList'], $userArray['lostGameList']);
        return $userArray;
    }

    public function addUser(Request $request, Response $response): Response
    {
        $json = $request->getBody();
        $userArray = json_decode($json, true);

        $initRating = 1500;

        $user = new User();
        $user->setCode($userArray['code']);
        $user->setName($userArray['name']);
        $user->setTeam("");
        $user->setRating($initRating);
        $user->setDeleted(false);

        $this->em->persist($user);
        $this->em->flush();

        return $response->withJson([]);
    }

    public function updateRatings(
        Request $request,
        Response $response
    ): Response {
        $json = $request->getBody();
        $usersCodes = json_decode($json, true);
        $winnerUserCode = $usersCodes['winnerUserCode'];
        $looserUserCode = $usersCodes['looserUserCode'];

        /** @var User $winnerUser */
        $winnerUser = $this->userRepository->findOneBy(['code' => $winnerUserCode]);
        /** @var User $looserUser */
        $looserUser = $this->userRepository->findOneBy(['code' => $looserUserCode]);

        $warningMsg = $this->getUpdateUserWarningMsg($winnerUser, $looserUser);
        if ($warningMsg) {
            return $response->withJson(['status' => 'warning', 'warningMsg' => $warningMsg]);
        }

        $ratingDiff = $this->updateRatingsInDb($winnerUser, $looserUser);
        return $response->withJson(['status' => 'success', 'ratingDiff' => $ratingDiff]);
    }

    private function getUpdateUserWarningMsg(?User $winnerUser, ?User $looserUser): ?string
    {
        if (!isset($winnerUser) && !isset($looserUser)) {
            return "Winner and looser does not exist";
        } else if (!isset($winnerUser)) {
            return "Winner does not exist";
        } else if (!isset($looserUser)) {
            return "Looser does not exist";
        } else if ($winnerUser->getUserNid() === $looserUser->getUserNid()) {
            return "Winner is same as looser";
        }

        return null;
    }

    private function updateRatingsInDb(
        User $winnerUser,
        User $looserUser
    ): int {
        $oldWinnerRating = $winnerUser->getRating();
        $oldLooserRating = $looserUser->getRating();

        [
            $newWinnerRating,
            $newLooserRating,
        ] = $this->calcNewRatings($oldWinnerRating, $oldLooserRating);
        $ratingDiff = $newWinnerRating - $oldWinnerRating;

        $winnerUser->setRating($newWinnerRating);
        $looserUser->setRating($newLooserRating);
        $game = $this->createGame($winnerUser, $looserUser, $oldWinnerRating,
            $oldLooserRating, $ratingDiff);

        $this->em->persist($game);
        $this->em->flush();

        return $ratingDiff;
    }

    private function calcNewRatings(
        int $oldWinnerRating,
        int $oldLooserRating
    ): array{
        $kfactor = 32;

        $winnerLooserDiff = $oldLooserRating - $oldWinnerRating;
        $pWinner = 1 / (1 + (10 ** ($winnerLooserDiff / 400))); // propability of winning winner user

        $ratingDiff = round($kfactor * (1 - $pWinner));
        $newWinnerRating = $oldWinnerRating + $ratingDiff;
        $newLooserRating = $oldLooserRating - $ratingDiff;

        return [$newWinnerRating, $newLooserRating];
    }

    private function createGame(
        User $winnerUser,
        User $looserUser,
        int $oldWinnerRating,
        int $oldLooserRating,
        int $ratingDiff
    ): Game {
        $game = new Game();
        $game->setWinnerUser($winnerUser);
        $game->setLooserUser($looserUser);
        $game->setWinnerRatingBefore($oldWinnerRating);
        $game->setLooserRatingBefore($oldLooserRating);
        $game->setRatingDiff($ratingDiff);

        return $game;
    }
}
