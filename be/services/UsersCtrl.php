<?php
class UsersCtrl {
    function __construct($db) {
        $this->db = $db;
    }

    public function getUsers() {
        $sql = '
        SELECT u.user_nid, u.name, u.code, u.rating, IFNULL(rh.games_no, 0) as games_no FROM users u 
        LEFT JOIN (
            SELECT user_nid, count(user_nid) as games_no FROM ratings_history
            GROUP BY user_nid
        ) rh ON u.user_nid = rh.user_nid
        ORDER BY u.rating DESC, u.code ASC
        ';

        return $this->db
            ->query($sql)
            ->fetchAll();
    }

    private function getUserRating($userNid) {
        $stm = $this->db->prepare('SELECT rating FROM users WHERE user_nid = :userNid');
        $stm->bindValue(':userNid', $userNid, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchColumn();
    }

    private function updateUserRating($userNid, $newRating) {
        $stmUpdate = $this->db->prepare('UPDATE users SET rating = :rating WHERE user_nid = :userNid');
        $stmUpdate->bindValue(':userNid', $userNid, PDO::PARAM_INT);
        $stmUpdate->bindValue(':rating', $newRating, PDO::PARAM_INT);
        $stmUpdate->execute();

        $stmInsert = $this->db->prepare('
        INSERT INTO ratings_history (user_nid, rating) VALUES (:userNid, :rating)');
        $stmInsert->bindValue(':userNid', $userNid, PDO::PARAM_INT);
        $stmInsert->bindValue(':rating', $newRating, PDO::PARAM_INT);
        $stmInsert->execute();
    }

    private function calcNewRatings($oldWinnerRating, $oldLooserRating) {
        $kfactor = 32;

        $transformetRatingWinner = pow(10, ($oldWinnerRating / 400));
        $transformetRatingLooser = pow(10, ($oldLooserRating / 400));
   
        $expectedScopeWinner = $transformetRatingWin / ($transformetRatingWin + $transformetRatingLoose);
        $expectedScopeLooser = $transformetRatingLoose / ($transformetRatingWin + $transformetRatingLoose);
   
        $newRatingWinner = round($oldWinnerRating + ($kfactor * (1 - $expectedScopeWin)));
        $newRatingLooser = round($oldLooserRating - ($kfactor * $expectedScopeLoose));

        return [$newRatingWinner, $newRatingLooser];
    }

    public function updateRatings($winnerUserNid, $looserUserNid) {
        $oldWinnerRating = $this->getUserRating($winnerUserNid); 
        $oldLooserRating = $this->getUserRating($looserUserNid);

        list($newWinnerRating, $newLooserRating) = $this->calcNewRatings($oldWinnerRating, $oldLooserRating);

        $this->updateUserRating($winnerUserNid, $newWinnerRating);
        $this->updateUserRating($looserUserNid, $newLooserRating);
    }
}