<?php

namespace Controller;

use Doctrine\ORM\NoResultException;
use Slim\Http\Response;

use Doctrine\ORM\EntityManager;
use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\GameRepository;
use Model\Repository\UserRepository;

class RemoveGameCtrl
{
    /** @var EntityManager */
    private $em;

    /** @var GameRepository */
    private $gameRepository;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        EntityManager $em,
        GameRepository $gameRepository,
        UserRepository $userRepository
    ) {
        $this->em = $em;
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;
    }

    private function getOpponentFromGame(Game $lastGame, int $userNid): User
    {
        $winnerUser = $lastGame->getWinnerUser();
        $looserUser = $lastGame->getLooserUser();

        $isWinner = $winnerUser->getUserNid() === $userNid;

        return $isWinner ? $looserUser : $winnerUser;
    }

    private function removeLastGame(
        Game $lastGame,
        User $user,
        User $opponentUser
    ): void {
        $isWinner = $lastGame->getWinnerUser()->getUserNid() === $user->getUserNid();

        $ratingDiff = $lastGame->getRatingDiff();
        $userNewRating = $user->getRating() + ($isWinner ? -$ratingDiff : $ratingDiff);
        $opponentUserNewRating = $opponentUser->getRating() + ($isWinner ? $ratingDiff : -$ratingDiff);

        $user->setRating($userNewRating);
        $opponentUser->setRating($opponentUserNewRating);

        $this->em->remove($lastGame);
        $this->em->flush();
    }

    private function removeLastGameIfPossibleInDb(string $code): array
    {
        $user = $this->userRepository->requireUserByCode($code);

        $userNid = $user->getUserNid();

        try {
            $lastGame = $this->gameRepository->requireLastGame($userNid);
        } catch (NoResultException $e) {
            return [
                'removed' => false,
                'warning' => 'Player has no games',
            ];
        }

        $opponentUser = $this->getOpponentFromGame($lastGame, $userNid);

        try {
            $opponentLastGame = $this->gameRepository->requireLastGame($opponentUser->getUserNid());
        } catch (NoResultException $e) {
            return [
                'removed' => false,
                'warning' => 'Opponent game was removed earlier.',
            ];
        }

        if ($lastGame->getGameNid() !== $opponentLastGame->getGameNid()) {
            return [
                'removed' => false,
                'warning' => 'Opponent has later games',
            ];
        }

        $this->removeLastGame($lastGame, $user, $opponentUser);

        return ['removed' => true];
    }

    public function removeLastGameIfPossible(
        Response $response,
        string $code
    ): Response {
        $respArray = $this->removeLastGameIfPossibleInDb($code);
        return $response->withJson($respArray);
    }
}