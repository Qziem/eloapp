<?php
namespace Controller;

use Doctrine\ORM\EntityManager;
use Util\Helpers;
use Entity\Game;
use Entity\User;

class UsersCtrl {
    function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function getUsers(): array {
        $usersEntities = $this->em->getRepository('Entity\User')->findBy(['deleted' => 0], ['rating' => 'DESC', 'code' => 'ASC']);
        return Helpers::entitiesListToArray($usersEntities);
    }

    public function addUser(array $userArr): void {
        $initRating = 1500;

        $user = new User();
        $user->setCode($userArr['code']);
        $user->setName($userArr['name']);
        $user->setRating($initRating);
        $user->setDeleted(false);

        $this->em->persist($user);
        $this->em->flush();

        $userNid = $user->getUserNid();
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

    public function updateRatings(int $winnerUserNid, int $looserUserNid): void {
        $winnerUser = $this->em->getRepository('Entity\User')->find($winnerUserNid);
        $looserUser = $this->em->getRepository('Entity\User')->find($looserUserNid);
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
}