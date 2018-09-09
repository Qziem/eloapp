<?php
class RatingsHistoryCtrl {
    function __construct($db) {
        $this->db = $db;
    }

    public function getRatingsHistory($userNid) {
        $stm = $this->db->prepare('
        SELECT rating, cdate FROM ratings_history
        WHERE user_nid = :userNid
        ORDER BY cdate DESC
        ');
        $stm->bindValue(':userNid', $userNid, PDO::PARAM_INT);

        $stm->execute();
        return $stm->fetchAll();
    }
}