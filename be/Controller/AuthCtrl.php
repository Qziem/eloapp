<?php
class AuthCtrl {
    static public function isLogged() {
        return isset($_SESSION['isLogged']);
    }

    static public function assertIsLogged() {
        if (!AuthCtrl::isLogged()) {
            throw new Exception('Not logged');
        }
    }

    public function login($password) {
        $accessPasseord = '613b88d193a2be96cb728060933ed74166db46f7';
        $isPass = sha1($password) === $accessPasseord;
        if ($isPass) {
            $_SESSION['isLogged'] = true;
        }

        return $isPass;
    }
}