<?php
namespace Controller;

class AuthCtrl {
    public function isLogged(): bool {
        return isset($_SESSION['isLogged']);
    }

    public function login(string $password): bool {
        $accessPassword = '31137594f95f0bc00c08a98caf14ed3b5905bbee';
        $isPass = sha1($password) === $accessPassword;
        if ($isPass) {
            $_SESSION['isLogged'] = true;
        }

        return $isPass;
    }
}