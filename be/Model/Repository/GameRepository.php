<?php

namespace Model\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Model\Entity\Game;

class GameRepository extends EntityRepository
{
    private function querySortedGamesForUser(int $userNid): Query
    {
        return $this->createQueryBuilder('g')
            ->where('g.winnerUserNid = :userNid')
            ->orWhere('g.looserUserNid = :userNid')
            ->orderBy('g.cdate', 'DESC')
            ->setParameter('userNid', $userNid)
            ->getQuery();
    }

    public function findSortedGamesForUser(int $userNid): array
    {
        return $this->querySortedGamesForUser($userNid)
            ->getResult();
    }

    /**
     * @param int $userNid
     * @return Game
     * @throws NoResultException
     */
    public function findLastGame(int $userNid): ?Game
    {
        $result = $this->querySortedGamesForUser($userNid)
            ->setMaxResults(1)
            ->getResult();

        return $result !== null ? $result[0] : null;
    }

    public function requireLastGame(int $userNid): Game
    {
        $lastGame = $this->findLastGame($userNid);
        if ($lastGame === null) {
            throw new \Error('Particular single game could not be found');
        }

        return $lastGame;
    }
}
