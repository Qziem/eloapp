<?php

namespace Middleware\Validation;

use \Psr\Http\Message\ServerRequestInterface as Request;
use Model\Repository\UserRepository;

class ValidationAddUser extends ValidationAbstract
{
    /** @var UserRepository */
    private $userRepository;
    
    function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    protected function validate(Request $request): ?string
    {
        $json = $request->getBody();
        $userArray = json_decode($json, true);
        $code = $userArray['code'];

        if (strlen($code) > 3) {
            return "Code can not be longer than 3 letters";
        }

        $user = $this->userRepository->findOneByCode($code);
        if ($user !== null) {
            return "User with given code already exists";
        }

        return null;
    }
}
