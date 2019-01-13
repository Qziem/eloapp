<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
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
            $userArray = $this->sliceGameLists($userArray);
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
            'winGameList' => $user->getWinGameList(),
            'looseGameList' => $user->getLooseGameList(),
        ];
    }

    private function sliceGameLists(array $userArray): array
    {
        $sliceDays = 3;
        $fromDate = new \DateTime();
        $fromDate->sub(new \DateInterval('P' . $sliceDays . 'D'));
        $fromDate->setTime(0, 0);

        $winGameList = $userArray['winGameList'];
        $looseGameList = $userArray['looseGameList'];

        $winGameList = array_filter($winGameList, function (Game $game) use ($fromDate) {
            return $game->getCdate() > $fromDate;
        });

        $looseGameList = array_filter($looseGameList, function (Game $game) use ($fromDate) {
            return $game->getCdate() > $fromDate;
        });

        $userArray['winGameList'] = $winGameList;
        $userArray['looseGameList'] = $looseGameList;

        return $userArray;
    }

    private function calculateTrendRatingDiff(array $userArray): array
    {
        $winGameList = $userArray['winGameList'];
        $looseGameList = $userArray['looseGameList'];

        $winGamesSumRating = $this->sumRatingDiffs($winGameList);
        $looseGamesSumRating = $this->sumRatingDiffs($looseGameList);

        $trendRatingDiff = $winGamesSumRating - $looseGamesSumRating;
        $userArray['trendRatingDiff'] = $trendRatingDiff;

        return $userArray;
    }

    private function sumRatingDiffs(array $gameList): int
    {
        return array_reduce($gameList, function (int $acc, Game $game) {
            return $acc + $game->getRatingDiff();
        }, 0);
    }

    private function removeGameListsFromUserArray(array $userArray): array
    {
        unset($userArray['winGameList']);
        unset($userArray['looseGameList']);

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
        $user->setRating($initRating);
        $user->setDeleted(false);

        $this->em->persist($user);
        $this->em->flush();

        return $response->withJson([]);
    }

    private function getGame(
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

    private function updateRatingsInDb(
        int $winnerUserNid,
        int $looserUserNid
    ): int {
        /** @var User $winnerUser */
        $winnerUser = $this->userRepository->find($winnerUserNid);
        /** @var User $looserUser */
        $looserUser = $this->userRepository->find($looserUserNid);

        if (!isset($winnerUser, $looserUser)) {
            throw new \InvalidArgumentException('Winner or looser does not exist');
        }

        if ($winnerUserNid === $looserUserNid) {
            throw new \InvalidArgumentException('Winner and looser nids are the same');
        }

        $oldWinnerRating = $winnerUser->getRating();
        $oldLooserRating = $looserUser->getRating();

        [
            $newWinnerRating,
            $newLooserRating,
        ] = $this->calcNewRatings($oldWinnerRating, $oldLooserRating);
        $ratingDiff = $newWinnerRating - $oldWinnerRating;

        $winnerUser->setRating($newWinnerRating);
        $looserUser->setRating($newLooserRating);
        $game = $this->getGame($winnerUser, $looserUser, $oldWinnerRating,
            $oldLooserRating, $ratingDiff);

        $this->em->persist($game);
        $this->em->flush();

        return $ratingDiff;
    }

    public function updateRatings(
        Request $request,
        Response $response
    ): Response {
        $json = $request->getBody();
        $usersCodes = json_decode($json, true);
        $winnerUserNid = $usersCodes['winnerUserNid'];
        $looserUserNid = $usersCodes['looserUserNid'];

        $ratingDiff = $this->updateRatingsInDb($winnerUserNid, $looserUserNid);

        return $response->withJson(['ratingDiff' => $ratingDiff]);
    }
}
