<?php
use Doctrine\ORM\EntityManager;
use Util\Helpers;

class RatingsHistoryCtrl {
    function __construct(EntityManager $em) {
        $this->em = $em;
    }

    private function getOponentName($user) {
        return "(" . $user->getCode() . ") " . $user->getName();
    }

    private function entitiesListToOutput($gamesEntities, $userNid) {
        $output = [];
        foreach($gamesEntities as $entity) {
            $entityWinnerUserNid = $entity->getWinnerUser()->getUserNid();

            $userRating = $entityWinnerUserNid === $userNid
                ? $entity->getWinnerRatingBefore()
                : $entity->getLooserRatingBefore();

            $oponentRating = $entityWinnerUserNid === $userNid
                ? $entity->getLooserRatingBefore()
                : $entity->getWinnerRatingBefore();

            $ratingDiff = $entityWinnerUserNid === $userNid
                ? $entity->getRatingDiff()
                : -$entity->getRatingDiff();

            $oponentName = $entityWinnerUserNid === $userNid
                ? $this->getOponentName($entity->getLooserUser())
                : $this->getOponentName($entity->getWinnerUser());

            $output[] = [
                'userRating' => $userRating,
                'oponentRating' => $oponentRating,
                'oponentName' => $oponentName,
                'ratingDiff' => $ratingDiff,
                'date' => $entity->getCdate()->format('Y-m-d H:i:s')
            ];
        }
        return $output;
    }

    public function getRatingsHistory($userNid) {
        $query = $this->em->createQuery(
            'SELECT g FROM Entity\Game g
            WHERE g.winnerUserNid = :userNid
            OR g.looserUserNid = :userNid
            ORDER BY g.cdate DESC'
        )->setParameter('userNid', $userNid);
        
        $gamesEntities = $query->getResult();

        return $this->entitiesListToOutput($gamesEntities, $userNid);
    }
}