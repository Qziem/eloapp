<?php

namespace Eloapp\Services;

class UsersCtrl
{
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getUsers()
    {
        $sql = '
        SELECT u.user_nid, u.name, u.code, u.rating
        FROM users u
        ORDER BY u.rating DESC, u.code ASC
        ';

        return $this->db
            ->query($sql)
            ->fetchAll();
    }

    private function insertIntoRatingsHistory($userNid, $rating)
    {
        $stmInsert = $this->db->prepare('
        INSERT INTO ratings_history (user_nid, rating) VALUES (:userNid, :rating)');
        $stmInsert->bindValue(':userNid', $userNid, PDO::PARAM_INT);
        $stmInsert->bindValue(':rating', $rating, PDO::PARAM_INT);
        $stmInsert->execute();
    }

    public function addUser($user)
    {
        $initRating = 1500;

        $stm1 = $this->db->prepare('INSERT INTO users (code, name, rating) VALUES (:code, :name, :rating)');
        $stm1->bindValue(':code', $user['code'], PDO::PARAM_STR);
        $stm1->bindValue(':name', $user['name'], PDO::PARAM_STR);
        $stm1->bindValue(':rating', $initRating, PDO::PARAM_INT);
        $stm1->execute();
        $userNid = $this->db->lastInsertId();

        $this->insertIntoRatingsHistory($userNid, $initRating);
    }

    private function getUserRating($userNid)
    {
        $stm = $this->db->prepare('SELECT rating FROM users WHERE user_nid = :userNid');
        $stm->bindValue(':userNid', $userNid, PDO::PARAM_INT);
        $stm->execute();

        return $stm->fetchColumn();
    }

    private function updateUserRating($userNid, $newRating)
    {
        $stmUpdate = $this->db->prepare('UPDATE users SET rating = :rating WHERE user_nid = :userNid');
        $stmUpdate->bindValue(':userNid', $userNid, PDO::PARAM_INT);
        $stmUpdate->bindValue(':rating', $newRating, PDO::PARAM_INT);
        $stmUpdate->execute();

        $this->insertIntoRatingsHistory($userNid, $newRating);
    }

    private function calcNewRatings($oldWinnerRating, $oldLooserRating)
    {
        $kfactor = 32;

        $transformetRatingWinner = pow(10, ($oldWinnerRating / 400));
        $transformetRatingLooser = pow(10, ($oldLooserRating / 400));

        $expectedScopeWinner = $transformetRatingWinner / ($transformetRatingWinner + $transformetRatingLooser);
        $expectedScopeLooser = $transformetRatingLooser / ($transformetRatingWinner + $transformetRatingLooser);

        $newRatingWinner = round($oldWinnerRating + ($kfactor * (1 - $expectedScopeWinner)));
        $newRatingLooser = round($oldLooserRating - ($kfactor * $expectedScopeLooser));

        return [$newRatingWinner, $newRatingLooser];
    }

    public function updateRatings($winnerUserNid, $looserUserNid)
    {
        $oldWinnerRating = $this->getUserRating($winnerUserNid);
        $oldLooserRating = $this->getUserRating($looserUserNid);
        if ($oldWinnerRating === false || $oldLooserRating === false) {
            throw new Exception('Winner or looser rating does not exist');
        }

        if ($winnerUserNid === $looserUserNid) {
            throw new Exception('Winner and looser nids are the same');
        }

        list($newWinnerRating, $newLooserRating) = $this->calcNewRatings($oldWinnerRating, $oldLooserRating);

        $this->updateUserRating($winnerUserNid, $newWinnerRating);
        $this->updateUserRating($looserUserNid, $newLooserRating);
    }
}
