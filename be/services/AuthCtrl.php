<?php
class AuthCtrl {
    function __construct($db) {
        $this->db = $db;
    }

    public function isLogged() {
        return false;
    }

    public function login($password) {
        return false;
    }
}