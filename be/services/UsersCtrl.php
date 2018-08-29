<?php
class UsersCtrl {
    function __construct($db) {
        $this->db = $db;
    }

    function getUsers() {
        $sql = '
        SELECT u.user_nid, u.name, u.code, u.rating, IFNULL(rh.games_no, 0) as games_no FROM users u 
        LEFT JOIN (
            SELECT user_nid, count(user_nid) as games_no FROM ratings_history
            GROUP BY user_nid
        ) rh ON u.user_nid = rh.user_nid
        ORDER BY u.rating DESC, u.code ASC
        ';

        $respArray = [];
        foreach ($this->db->query($sql) as $row) {
            $respArray[] = $row;
        }

        return $respArray;
    }
}