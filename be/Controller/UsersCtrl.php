<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\UserRepository;
use Slim\Http\Response;
use Util\Helpers;
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
        $usersEntities = $this->userRepository->findBy(['deleted' => 0],
            ['rating' => 'DESC', 'code' => 'ASC']);
        $respArray = Helpers::entitiesListToArray($usersEntities);
        return $response->withJson($respArray);
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

    public function updateRatings(
        Request $request,
        Response $response
    ): Response {
        $json = $request->getBody();
        $usersCodes = json_decode($json, true);
        $winnerUserCode = $usersCodes['winnerUserCode'];
        $looserUserCode = $usersCodes['looserUserCode'];

        $this->valdateUpdateRatingsCodes($winnerUserCode, $looserUserCode);

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

    private function valdateUpdateRatingsCodes(string $winnerUserCode, string $looserUserCode): void
    {
        if (!$winnerUserCode) {
            throw new \InvalidArgumentException('Winner code is empty');
        }
        if (!$looserUserCode) {
            throw new \InvalidArgumentException('Looser code is empty');
        }
        if ($winnerUserCode === $looserUserCode) {
            throw new \InvalidArgumentException('Winner and looser are the same');
        }
    }

    private function getUpdateUserWarningMsg(?User $winnerUser, ?User $looserUser): ?string
    {
        if (!isset($winnerUser) && !isset($looserUser)) {
            return "Winner and looser does not exist";
        } else if (!isset($winnerUser)) {
            return "Winner does not exist";
        } else if (!isset($looserUser)) {
            return "Looser does not exist";
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
