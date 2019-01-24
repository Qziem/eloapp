<?php

namespace Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\GameRepository;
use Model\Repository\UserRepository;
use Slim\Http\Response;

class RemoveGameSvc
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

    public function removeLastGameIfPossible(string $code): array
    {
        $user = $this->userRepository->findOneByCode($code);
        if ($user === null) {
            return [
                'status' => 'warning',
                'warningMsg' => 'Player does not exist',
            ];
        }

        $userNid = $user->getUserNid();

        $lastGame = $this->gameRepository->findLastGame($userNid);
        if ($lastGame === null) {
            return [
                'status' => 'warning',
                'warningMsg' => 'Player has no games',
            ];
        }

        $opponentUser = $this->getOpponentFromGame($lastGame, $userNid);
        $opponentLastGame = $this->gameRepository->requireLastGame($opponentUser->getUserNid());

        if ($lastGame->getGameNid() !== $opponentLastGame->getGameNid()) {
            return [
                'status' => 'warning',
                'warningMsg' => 'Opponent has later games',
            ];
        }

        $this->removeLastGame($lastGame, $user, $opponentUser);
        return ['status' => 'success'];
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
}