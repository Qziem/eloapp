<?php
use Doctrine\ORM\EntityManager;
use Util\Helpers;

class UsersCtrl {
    function __construct($db, EntityManager $em) {
        $this->db = $db;
        $this->em = $em;
    }

    public function getUsers() {
        $usersEntities = $this->em->getRepository('Entity\User')->findAll();
        return Helpers::entitiesListToArray($usersEntities);
    }

    private function insertIntoRatingsHistory($userNid, $rating) {
        $stmInsert = $this->db->prepare('
        INSERT INTO ratings_history (user_nid, rating) VALUES (:userNid, :rating)');
        $stmInsert->bindValue(':userNid', $userNid, PDO::PARAM_INT);
        $stmInsert->bindValue(':rating', $rating, PDO::PARAM_INT);
        $stmInsert->execute();
    }

    public function addUser($userArr) {
        $initRating = 1500;

        $user = new Entity\User();
        $user->setCode($userArr['code']);
        $user->setName($userArr['name']);
        $user->setRating($initRating);

        $this->em->persist($user);
        $this->em->flush();

        $userNid = $user->getUserNid();
        $this->insertIntoRatingsHistory($userNid, $initRating);
    }

    private function getUserRating($userNid) {
        $user = $this->em->getRepository('Entity\User')->find($userNid);
        return $user->getRating();
    }

    private function updateUserRating($userNid, $newRating) {
        $user = $this->em->getRepository('Entity\User')->find($userNid);
        $user->setRating($newRating);
        $this->em->flush();

        $this->insertIntoRatingsHistory($userNid, $newRating);
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
        $oldWinnerRating = $this->getUserRating($winnerUserNid); 
        $oldLooserRating = $this->getUserRating($looserUserNid);
        if ($oldWinnerRating === false || $oldLooserRating === false) throw new Exception('Winner or looser rating does not exist');
        if ($winnerUserNid === $looserUserNid) throw new Exception("Winner and looser nids are the same");

        list($newWinnerRating, $newLooserRating) = $this->calcNewRatings($oldWinnerRating, $oldLooserRating);

        $this->updateUserRating($winnerUserNid, $newWinnerRating);
        $this->updateUserRating($looserUserNid, $newLooserRating);
    }
}