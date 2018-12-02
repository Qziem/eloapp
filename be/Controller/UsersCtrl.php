<?php
namespace Controller;

use Doctrine\ORM\EntityManager;
use Util\Helpers;

class UsersCtrl {
    function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function getUsers() {
        $usersEntities = $this->em->getRepository('Entity\User')->findBy(['deleted' => 0], ['rating' => 'DESC', 'code' => 'ASC']);
        return Helpers::entitiesListToArray($usersEntities);
    }

    public function addUser($userArr) {
        $initRating = 1500;

        $user = new Entity\User();
        $user->setCode($userArr['code']);
        $user->setName($userArr['name']);
        $user->setRating($initRating);
        $user->setDeleted(false);

        $this->em->persist($user);
        $this->em->flush();

        $userNid = $user->getUserNid();
    }

     private function getGame($winnerUser, $looserUser, $oldWinnerRating, $oldLooserRating, $ratingDiff) {
        $game = new Entity\Game();
        $game->setWinnerUser($winnerUser);
        $game->setLooserUser($looserUser);
        $game->setWinnerRatingBefore($oldWinnerRating);
        $game->setLooserRatingBefore($oldLooserRating);
        $game->setRatingDiff($ratingDiff);
     
        return $game;
    }

    private function calcNewRatings($oldWinnerRating, $oldLooserRating) {
        $kfactor = 32;

        $transformetRatingWinner = pow(10, ($oldWinnerRating / 400));
        $transformetRatingLooser = pow(10, ($oldLooserRating / 400));
   
        $expectedScopeWinner = $transformetRatingWinner / ($transformetRatingWinner + $transformetRatingLooser);
        $expectedScopeLooser = $transformetRatingLooser / ($transformetRatingWinner + $transformetRatingLooser);
   
        $newRatingWinner = round($oldWinnerRating + ($kfactor * (1 - $expectedScopeWinner)));
        $newRatingLooser = round($oldLooserRating - ($kfactor * $expectedScopeLooser));

        return [$newRatingWinner, $newRatingLooser];
    }

    public function updateRatings($winnerUserNid, $looserUserNid) {
        $winnerUser = $this->em->getRepository('Entity\User')->find($winnerUserNid);
        $looserUser = $this->em->getRepository('Entity\User')->find($looserUserNid);
        if (!isset($winnerUser) || !isset($looserUser)) throw new Exception('Winner or looser does not exist');
        if ($winnerUserNid === $looserUserNid) throw new Exception("Winner and looser nids are the same");

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