<?php
namespace Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Entity\Game;

class GameRepository extends EntityRepository {
  private function querySortedGamesForUser(int $userNid): Query {
    return $this->createQueryBuilder('g')
      ->where('g.winnerUserNid = :userNid')
      ->orWhere('g.looserUserNid = :userNid')
      ->orderBy('g.cdate', 'DESC')
      ->setParameter('userNid', $userNid)
      ->getQuery();
  }

  public function findSortedGamesForUser(int $userNid): array {
    return $this->querySortedGamesForUser($userNid)
      ->getResult();
  }

  public function findLastGame(int $userNid): ?Game {
    $games = $this->querySortedGamesForUser($userNid)
      ->setMaxResults(1)
      ->getResult();

    return isset($games[0]) ? $games[0] : null ;
  }
}