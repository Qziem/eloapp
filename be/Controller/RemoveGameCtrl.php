<?php
namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Doctrine\ORM\EntityManager;
use Util\Helpers;
use Model\Entity\Game;
use Model\Entity\User;

class RemoveGameCtrl {
    function __construct(EntityManager $em) {
        $this->em = $em;
    }

    private function getOponentFromGame(Game $lastGame, int $userNid): ?User {
      $winnerUser = $lastGame->getWinnerUser();
      $looserUser = $lastGame->getLooserUser();

      $isWinner = $winnerUser->getUserNid() === $userNid;

      return $isWinner ? $looserUser : $winnerUser;
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

    private function removeLastGameIfPossibleInDb(string $code): array {
      $userRepository = $this->em->getRepository(User::class);
      $user = $userRepository->findUserByCode($code);
      if (!isset($user)) {
        return [
          'removed' => false,
          'warning' => 'Player does not exist',
        ];
      }

      $userNid = $user->getUserNid();
      $gameRepository = $this->em->getRepository(Game::class);
      $lastGame = $gameRepository->findLastGame($userNid);

      if (!isset($lastGame)) {
        return [
          'removed' => false,
          'warning' => 'Player has no games',
        ];
      }

      $oponentUser = $this->getOponentFromGame($lastGame, $userNid);

      $oponentLastGame = $gameRepository->findLastGame($oponentUser->getUserNid());
      if ($lastGame->getGameNid() !== $oponentLastGame->getGameNid()) {
        return [
          'removed' => false,
          'warning' => 'Oponent has later games',
        ];
      }

      $this->removeLastGame($lastGame, $user, $oponentUser);
      return ['removed' => true];
    }

    public function removeLastGameIfPossible(Response $response, $code): Response {
      $respArray = $this->removeLastGameIfPossibleInDb($code);
      return $response->withJson($respArray);
    }
}