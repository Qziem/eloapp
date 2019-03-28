<?php declare(strict_types=1);

namespace Service;

use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\GameRepository;
use Model\Repository\UserRepository;

class StatisticsChartDataSvc
{
    private const DEFAULT_USER_AMOUNT = 5;

    /** @var UserRepository */
    private $userRepository;

    /** @var GameRepository */
    private $gameRepository;

    public function __construct(
        UserRepository $userRepository,
        GameRepository $gameRepository
    ) {
        $this->userRepository = $userRepository;
        $this->gameRepository = $gameRepository;
    }

    /**
     * @param int[] $userNidList
     * @return int[][]
     */
    public function loadData(array $userNidList): array
    {
        $users = $this->loadUsers($userNidList);
        $data = $this->loadDataFromDb($users);

        return $this->normalizeData($users, $data);
    }

    /**
     * @param int[] $userNidList
     * @return User[]
     */
    private function loadUsers(array $userNidList): array
    {
        $userParams = ['deleted' => 0];
        $usersAmount = \count($userNidList);
        $limit = self::DEFAULT_USER_AMOUNT;

        if ($usersAmount > 0) {
            $userParams['userNid'] = $userNidList;
            $limit = $usersAmount;
        }

        /** @var User[] $userEntityList */
        $userEntityList = $this->userRepository->findBy(
            $userParams,
            ['rating' => 'DESC'],
            $limit
        );

        $users = [];

        foreach ($userEntityList as $user) {
            $users[$user->getUserNid()] = $user;
        }

        return $users;
    }

    /**
     * @param User[] $users
     * @return int[][]
     */
    private function loadDataFromDb(array $users): array
    {
        $userNids = \array_keys($users);
        $games = $this->gameRepository->findByUsers($userNids);

        $data = [];

        foreach ($games as $game) {
            $data = $this->setDataForSingleGame($game, $users, $data);
        }

        return $data;
    }

    private function setDataForSingleGame(Game $game, array $users, array $data): array
    {
        $gameDate = $game->getCdate()->format('d M');
        $looserNid = $game->getLooserUser()->getUserNid();
        $winnerNid = $game->getWinnerUser()->getUserNid();
        $looserPoints = $game->getLooserRatingBefore() - $game->getRatingDiff();
        $winnerPoints = $game->getWinnerRatingBefore() + $game->getRatingDiff();

        $data = $this->setPoints($looserNid, $gameDate, $looserPoints, $users, $data);
        $data = $this->setPoints($winnerNid, $gameDate, $winnerPoints, $users, $data);

        return $data;
    }

    private function setPoints(
        int $userNid,
        string $gameDate,
        int $pointsAmount,
        array $users,
        array $data
    ): array {
        if (isset($users[$userNid])
            && $data[$gameDate][$userNid] < $pointsAmount
        ) {
            $data[$gameDate][$userNid] = $pointsAmount;
        }

        return $data;
    }

    /**
     * @param User[] $users
     * @param int[][] $data
     * @return mixed[][][]
     */
    private function normalizeData(array $users, array $data): array
    {
        $output = [];
        $points = [];

        foreach ($users as $user) {
            $points[$user->getCode()] = 1500; // @TODO get this start value from constant
        }

        foreach ($data as $date => $datum) {
            foreach ($datum as $userNid => $pointsAmount) {
                $code = $users[$userNid]->getCode();

                $points[$code] = $pointsAmount;
            }

            $output[] = array_merge(
                ['date' => $date],
                $points
            );
        }

        return $output;
    }
}