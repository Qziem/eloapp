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

    private function getUserParam($userCode, $param) {
        $stm = $this->db->prepare('SELECT ' . $param . ' FROM users WHERE code = :code');
        $stm->bindValue(':code', $userCode, PDO::PARAM_STR);
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

        $transformetRatingWin = pow(10, ($oldWinnerRating / 400));
        $transformetRatingLoose = pow(10, ($oldLooserRating / 400));
   
        $expectedScopeWin = $transformetRatingWin / ($transformetRatingWin + $transformetRatingLoose);
        $expectedScopeLoose = $transformetRatingLoose / ($transformetRatingWin + $transformetRatingLoose);
   
        $newRatingWin = round($oldWinnerRating + ($kfactor * (1 - $expectedScopeWin)));
        $newRatingLoose = round($oldLooserRating - ($kfactor * $expectedScopeLoose));

        return [$newRatingWin, $newRatingLoose];
    }

    public function updateRatings($userWinnerCode, $userLooserCode) {
        $oldWinnerRating = $this->getUserParam($userWinnerCode, 'rating'); 
        $oldLooserRating = $this->getUserParam($userLooserCode, 'rating');
        $winnerUserNid = $this->getUserParam($userWinnerCode, 'user_nid'); 
        $looserUserNid = $this->getUserParam($userLooserCode, 'user_nid'); 

        list($newRatingWin, $newRatingLoose) = $this->calcNewRatings($oldWinnerRating, $oldLooserRating);

        $this->updateUserRating($winnerUserNid, $newRatingWin);
        $this->updateUserRating($looserUserNid, $newRatingLoose);
    }
}