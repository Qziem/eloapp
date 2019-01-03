<?php
namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Model\Entity\User;
use Model\Repository\GameRepository;

class RatingsHistoryCtrl {
    /** @var GameRepository  */
    private $gameRepository;

    public function __construct(GameRepository $gameRepository) {
        $this->gameRepository = $gameRepository;
    }

    private function getOponentName(User $user): string {
        return \strtoupper($user->getCode());
    }

    private function entitiesListToOutput(array $gamesEntities, int $userNid): array {
        $output = [];
        foreach($gamesEntities as $entity) {
            $entityWinnerUserNid = $entity->getWinnerUser()->getUserNid();

            $userRating = $entityWinnerUserNid === $userNid
                ? $entity->getWinnerRatingBefore()
                : $entity->getLooserRatingBefore();

            $opponentRating = $entityWinnerUserNid === $userNid
                ? $entity->getLooserRatingBefore()
                : $entity->getWinnerRatingBefore();

            $ratingDiff = $entityWinnerUserNid === $userNid
                ? $entity->getRatingDiff()
                : -$entity->getRatingDiff();

            $opponentName = $entityWinnerUserNid === $userNid
                ? $this->getOponentName($entity->getLooserUser())
                : $this->getOponentName($entity->getWinnerUser());

            $output[] = [
                'userRating' => $userRating,
                'oponentRating' => $opponentRating,
                'oponentName' => $opponentName,
                'ratingDiff' => $ratingDiff,
                'date' => $entity->getCdate()->format('d M H:i')
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