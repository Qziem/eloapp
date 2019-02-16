<?php

namespace Service;

use Model\Repository\PasswordRepository;

class AuthSvc
{
    /** @var PasswordRepository */
    private $passwordRepository;

    public function __construct(PasswordRepository $passwordRepository)
    {
        $this->passwordRepository = $passwordRepository;
    }

    public function checkIsLogged(): bool
    {
        return isset($_SESSION['isLogged']);
    }

    public function doLogin(string $password): bool
    {
        $accessPassword = $this->passwordRepository->findPassword();

        $isSuccess = sha1($password) === $accessPassword;
        if ($isSuccess) {
            $_SESSION['isLogged'] = true;
        }

        return $isSuccess;
    }
}
