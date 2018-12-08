<?php
namespace Controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Doctrine\ORM\EntityManager;
use Util\Helpers;
use Model\Entity\Game;
use Model\Entity\User;

class UsersCtrl {
    function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function getUsers(Request $request, Response $response): Response {
        $usersEntities = $this->em->getRepository(User::class)->findBy(['deleted' => 0], ['rating' => 'DESC', 'code' => 'ASC']);
        $respArray = Helpers::entitiesListToArray($usersEntities);
        return $response->withJson($respArray);
    }

    public function addUser(Request $request, Response $response): Response {
        $json = $request->getBody();
        $userArray = json_decode($json, true);

        $initRating = 1500;

        $user = new User();
        $user->setCode($userArray['code']);
        $user->setName($userArray['name']);
        $user->setRating($initRating);
        $user->setDeleted(false);

        $this->em->persist($user);
        $this->em->flush();

        return $response->withJson([]);
    }

     private function getGame(User $winnerUser, User $looserUser, int $oldWinnerRating, int $oldLooserRating, int $ratingDiff): Game {
        $game = new Game();
        $game->setWinnerUser($winnerUser);
        $game->setLooserUser($looserUser);
        $game->setWinnerRatingBefore($oldWinnerRating);
        $game->setLooserRatingBefore($oldLooserRating);
        $game->setRatingDiff($ratingDiff);
     
        return $game;
    }

    private function calcNewRatings(int $oldWinnerRating, int $oldLooserRating): array {
        $kfactor = 32;

        $transformetRatingWinner = pow(10, ($oldWinnerRating / 400));
        $transformetRatingLooser = pow(10, ($oldLooserRating / 400));
   
        $expectedScopeWinner = $transformetRatingWinner / ($transformetRatingWinner + $transformetRatingLooser);
        $expectedScopeLooser = $transformetRatingLooser / ($transformetRatingWinner + $transformetRatingLooser);
   
        $newRatingWinner = round($oldWinnerRating + ($kfactor * (1 - $expectedScopeWinner)));
        $newRatingLooser = round($oldLooserRating - ($kfactor * $expectedScopeLooser));

        return [$newRatingWinner, $newRatingLooser];
    }

    private function updateRatingsInDb(int $winnerUserNid, int $looserUserNid): void {
        $userRepository = $this->em->getRepository(User::class);
        $winnerUser = $userRepository->find($winnerUserNid);
        $looserUser = $userRepository->find($looserUserNid);
        if (!isset($winnerUser) || !isset($looserUser)) throw new \Exception('Winner or looser does not exist');
        if ($winnerUserNid === $looserUserNid) throw new \Exception("Winner and looser nids are the same");

        $oldWinnerRating = $winnerUser->getRating(); 
        $oldLooserRating = $looserUser->getRating();

        list($newWinnerRating, $newLooserRating) = $this->calcNewRatings($oldWinnerRating, $oldLooserRating);
        $ratingDiff = $newWinnerRating - $oldWinnerRating;

        $winnerUser->setRating($newWinnerRating);
        $looserUser->setRating($newLooserRating);
        $game = $this->getGame($winnerUser, $looserUser, $oldWinnerRating, $oldLooserRating, $ratingDiff);

        $this->em->persist($game);
        $this->em->flush();
    }

    public function updateRatings(Request $request, Response $response): Response {
        $json = $request->getBody();
        $usersCodes = json_decode($json, true);
        $winnerUserNid = $usersCodes['winnerUserNid'];
        $looserUserNid = $usersCodes['looserUserNid'];
    
        $this->updateRatingsInDb($winnerUserNid, $looserUserNid);
        return $response->withJson([]);
    }
}