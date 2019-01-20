<?php

namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;

use Model\Entity\User;
use Model\Repository\GameRepository;
use Model\Repository\UserRepository;

class RatingsHistoryCtrl
{
    /** @var GameRepository */
    private $gameRepository;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(GameRepository $gameRepository, UserRepository $userRepository)
    {
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;
    }

    public function getRatingsHistory(
        Response $response,
        $code
    ): Response {
        /** @var User $user */
        $user = $this->userRepository->findOneByCode($code);

        if ($user === null) {
            $warningMsg = "User does not exist";
            return $response->withJson(['status' => 'warning', 'warningMsg' => $warningMsg]);
        }

        $userNid = $user->getUserNid();
        $gamesEntities = $this->gameRepository->findSortedGamesForUser($userNid);
        $ratingsHistoryArray = $this->entitiesListToArray($gamesEntities, $userNid);
 
        return $response->withJson(['status' => 'success', 'ratingsHistory' => $ratingsHistoryArray]);
    }

    private function entitiesListToArray(
        array $gamesEntities,
        int $userNid
    ): array {
        $output = [];
        foreach ($gamesEntities as $entity) {
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
                'date' => $entity->getCdate()->format('d M H:i'),
            ];
        }
        return $output;
    }

    private function getOponentName(User $user): string
    {
        return \strtoupper($user->getCode());
    }
}
