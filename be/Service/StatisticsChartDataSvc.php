<?php declare(strict_types=1);

namespace Service;

use Model\Entity\Game;
use Model\Entity\User;
use Model\Repository\GameRepository;
use Model\Repository\UserRepository;


class StatisticsChartDataSvc
{
    private const DEFAULT_USER_AMOUNT = 5;

    /** @var int[][] */
    private $data = [];

    /** @var User[] */
    private $users = [];

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
        $this->loadUsers($userNidList);
        $this->loadDataFromDb();

        return $this->normalizeData();
    }

    /**
     * @param int[] $userNidList
     * @return void
     */
    private function loadUsers(array $userNidList): void
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

        foreach ($userEntityList as $user) {
            $this->users[$user->getUserNid()] = $user;
        }
    }

    private function loadDataFromDb(): void
    {
        $userNids = \array_keys($this->users);
        $games = $this->gameRepository->findByUsers($userNids);

        foreach ($games as $game) {
            $this->setDataForSingleGame($game);
        }
    }

    private function setDataForSingleGame(Game $game): void
    {
        $gameDate = $game->getCdate()->format('d M');
        $looserNid = $game->getLooserUser()->getUserNid();
        $winnerNid = $game->getWinnerUser()->getUserNid();
        $looserPoints = $game->getLooserRatingBefore() - $game->getRatingDiff();
        $winnerPoints = $game->getWinnerRatingBefore() + $game->getRatingDiff();

        $this->setPoints($looserNid, $gameDate, $looserPoints);
        $this->setPoints($winnerNid, $gameDate, $winnerPoints);
    }

    private function setPoints(int $userNid, string $gameDate, int $pointsAmount): void
    {
        if (isset($this->users[$userNid])
            && $this->data[$gameDate][$userNid] < $pointsAmount
        ) {
            $this->data[$gameDate][$userNid] = $pointsAmount;
        }
    }

    /**
     * @return mixed[][][]
     */
    private function normalizeData(): array
    {
        $output = [];
        $points = [];

        foreach ($this->users as $user) {
            $points[$user->getCode()] = 1500; // @TODO get this start value from constant
        }

        foreach ($this->data as $date => $datum) {
            foreach ($datum as $userNid => $pointsAmount) {
                $code = $this->users[$userNid]->getCode();

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