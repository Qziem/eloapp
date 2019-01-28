<?php

namespace Model\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
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
    public function requireLastGame(int $userNid): Game
    {
        try {
            return $this->querySortedGamesForUser($userNid)
                ->setMaxResults(1)
                ->getSingleResult();
        } catch (NonUniqueResultException $e) {
            throw new \Error(''
                . 'Particular single game could not be found because to small '
                . 'amount of conditions'
            );
        }
    }
}