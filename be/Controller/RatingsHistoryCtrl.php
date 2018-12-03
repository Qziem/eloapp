<?php
namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Doctrine\ORM\EntityManager;
use Util\Helpers;
use Entity\User;

class RatingsHistoryCtrl {
    function __construct(EntityManager $em) {
        $this->em = $em;
    }

    private function getOponentName(User $user): string {
        return "(" . $user->getCode() . ") " . $user->getName();
    }

    private function entitiesListToOutput(array $gamesEntities, int $userNid): array {
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

    public function getRatingsHistory(Request $request, Response $response, $userNid): Response {
        $ratingsHistoryCtrl = new RatingsHistoryCtrl($this->em);
        $gameRepository = $this->em->getRepository('Entity\Game');
        $gamesEntities = $gameRepository->findSortedGamesForUser($userNid);

        $respArray = $this->entitiesListToOutput($gamesEntities, $userNid);
        return $response->withJson($respArray);
    }
}