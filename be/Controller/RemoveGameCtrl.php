<?php
namespace Controller;

use Doctrine\ORM\EntityManager;
use Util\Helpers;
use Entity\Game;
use Entity\User;

class RemoveGameCtrl {
    function __construct(EntityManager $em) {
        $this->em = $em;
    }

    private function getLastGame(int $userNid): ?Game {
      $dql = 'SELECT g FROM Entity\Game g
      WHERE g.winnerUserNid = :userNid
      OR g.looserUserNid = :userNid
      ORDER BY g.cdate DESC';

      $games = $this->em->createQuery($dql)
        ->setParameter('userNid', $userNid)
        ->setMaxResults(1)
        ->getResult();

      return isset($games[0]) ? $games[0] : null ;
    }

    private function getUserAndOponentFromGame(Game $lastGame, int $userNid): array {
      $winnerUser = $lastGame->getWinnerUser();
      $looserUser = $lastGame->getLooserUser();

      $isWinner = $winnerUser->getUserNid() === $userNid;

      $user = $isWinner ? $winnerUser : $looserUser;
      $oponentUser = $isWinner ? $looserUser : $winnerUser;

      return [$user, $oponentUser];
    }

    private function removeLastGame(Game $lastGame, User $user, User $oponentUser): void {
      $isWinner = $lastGame->getWinnerUser()->getUserNid() === $user->getUserNid();
      
      $ratingDiff = $lastGame->getRatingDiff();
      $userNewRating = $user->getRating() + ($isWinner ? -$ratingDiff : $ratingDiff);
      $oponentUserNewRating = $oponentUser->getRating() + ($isWinner ? $ratingDiff : -$ratingDiff);

      $user->setRating($userNewRating);
      $oponentUser->setRating($oponentUserNewRating);

      $this->em->remove($lastGame);
      $this->em->flush();
    }

    public function removeLastGameIfPossible(int $userNid): array {
      $lastGame = $this->getLastGame($userNid);

      if (!isset($lastGame)) {
        return [
          'removed' => false,
          'warning' => 'Player has no games',
        ];
      }

      list($user, $oponentUser) = $this->getUserAndOponentFromGame($lastGame, $userNid);

      $oponentLastGame = $this->getLastGame($oponentUser->getUserNid());
      if ($lastGame->getGameNid() !== $oponentLastGame->getGameNid()) {
        return [
          'removed' => false,
          'warning' => 'Oponent has later games',
        ];
      }

      $this->removeLastGame($lastGame, $user, $oponentUser);
      return ['removed' => true];
    }
}