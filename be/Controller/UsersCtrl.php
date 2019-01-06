<?php

namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use Doctrine\ORM\EntityManager;
use Util\Helpers;
use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\UserRepository;

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
    ): array {
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