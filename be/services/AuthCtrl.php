<?php

namespace Eloapp\services;

class AuthCtrl
{
    public static function isLogged($session)
    {
        return true;

        return $session->get('isLogged', false);
    }

    public static function assertIsLogged($session)
    {
        if (!AuthCtrl::isLogged($session)) {
            throw new \Exception('Not logged');
        }
    }

    public function login($password, $session)
    {
        $accessPasseord = '613b88d193a2be96cb728060933ed74166db46f7';
        $isPass = sha1($password) === $accessPasseord;
        if ($isPass) {
            $session->set('isLogged', true);
        }

        return $isPass;
    }
}
