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

    private function getUserRating($userCode) {
        $stm = $this->db->prepare('SELECT rating FROM users WHERE code = :code');
        $stm->bindValue(':code', $userCode, PDO::PARAM_STR);
        $stm->execute();
        return $stm->fetchColumn();
    }

    private function updateUserRating($userCode, $newRating) {
        $stm = $this->db->prepare('UPDATE users SET rating = :rating WHERE code = :code');
        $stm->bindValue(':rating', $newRating, PDO::PARAM_INT);
        $stm->bindValue(':code', $userCode, PDO::PARAM_STR);
        $stm->execute();
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

    public function updateRatings($userWinCode, $userLooseCode) {
        $oldWinnerRating = $this->getUserRating($userWinCode); 
        $oldLooserRating = $this->getUserRating($userLooseCode); 
        
        list($newRatingWin, $newRatingLoose) = $this->calcNewRatings($oldWinnerRating, $oldLooserRating);

        $this->updateUserRating($userWinCode, $newRatingWin);
        $this->updateUserRating($userLooseCode, $newRatingLoose);
    }
}