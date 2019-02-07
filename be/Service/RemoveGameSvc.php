<?php

namespace Service;

use Doctrine\ORM\EntityManager;
use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\GameRepository;
use Model\Repository\UserRepository;

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

    public function removeLastGame(string $code): void
    {
        $user = $this->userRepository->requireUserByCode($code);
        $userNid = $user->getUserNid();
        $lastGame = $this->gameRepository->requireLastGame($userNid);
        $opponentUser = $this->getOpponentFromGame($lastGame, $userNid);

        $isWinner = $lastGame->getWinnerUser()->getUserNid() === $user->getUserNid();

        $ratingDiff = $lastGame->getRatingDiff();
        $userNewRating = $user->getRating() + ($isWinner ? -$ratingDiff : $ratingDiff);
        $opponentUserNewRating = $opponentUser->getRating() + ($isWinner ? $ratingDiff : -$ratingDiff);

        $user->setRating($userNewRating);
        $opponentUser->setRating($opponentUserNewRating);

        $this->em->remove($lastGame);
        $this->em->flush();
    }

    public function getOpponentFromGame(Game $lastGame, int $userNid): User
    {
        $winnerUser = $lastGame->getWinnerUser();
        $looserUser = $lastGame->getLooserUser();

        $isWinner = $winnerUser->getUserNid() === $userNid;

        return $isWinner ? $looserUser : $winnerUser;
    }
}