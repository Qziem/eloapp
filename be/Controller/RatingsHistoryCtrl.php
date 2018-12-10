<?php
namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Doctrine\ORM\EntityManager;
use Util\Helpers;
use Model\Entity\User;
use Model\Repository\GameRepository;

class RatingsHistoryCtrl {
    function __construct(EntityManager $em, GameRepository $gameRepository) {
        $this->em = $em;
        $this->gameRepository = $gameRepository;
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
        $gamesEntities = $this->gameRepository->findSortedGamesForUser($userNid);

        $respArray = $this->entitiesListToOutput($gamesEntities, $userNid);
        return $response->withJson($respArray);
    }
}