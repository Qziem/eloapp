<?php

namespace Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\UserRepository;
use Model\Factory\GameFactory;

class UsersSvc
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var UserRepository */
    private $userRepository;

    /** @var GameFactory */
    private $gameFactory;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        GameFactory $gameFactory
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->gameFactory = $gameFactory;
    }

    public function getUsers(): array
    {
        $userEntityList = $this->userRepository->findBy(
            ['deleted' => 0],
            ['rating' => 'DESC', 'code' => 'ASC']
        );

        return array_map(function (User $user) {
            $userArray = $this->userToArray($user);
            $userArray = $this->calculateTrendRatingDiff($userArray);
            return $this->removeGameListsFromUserArray($userArray);
        }, $userEntityList);
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
        return array_reduce($gameList->toArray(), static function (int $acc, Game $game) {
            return $acc + $game->getRatingDiff();
        }, 0);
    }

    private function removeGameListsFromUserArray(array $userArray): array
    {
        unset($userArray['wonGameList'], $userArray['lostGameList']);
        return $userArray;
    }

    public function addUser(array $userArray): void
    {      
        $initRating = 1500;

        $user = new User();
        $user->setCode($userArray['code']);
        $user->setName($userArray['name']);
        $user->setTeam('');
        $user->setRating($initRating);
        $user->setDeleted(false);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function updateRatings(
        string $winnerUserCode,
        string $looserUserCode
    ): int {
        $winnerUser = $this->userRepository->requireUserByCode($winnerUserCode);
        $looserUser = $this->userRepository->requireUserByCode($looserUserCode);

        $game = $this->gameFactory->createGameEntity($winnerUser, $looserUser);
        $this->em->persist($game);
        
        $ratingDiff = $game->getRatingDiff();
        $newWinnerRating = $winnerUser->getRating() + $ratingDiff;
        $newLooserRating = $looserUser->getRating() - $ratingDiff;
        $winnerUser->setRating($newWinnerRating);
        $looserUser->setRating($newLooserRating);

        $this->em->flush();

        return $ratingDiff;
    }
}
