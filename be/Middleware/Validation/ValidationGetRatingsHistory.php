<?php

namespace Middleware\Validation;

use \Psr\Http\Message\ServerRequestInterface as Request;
use Model\Repository\UserRepository;

class ValidationGetRatingsHistory extends ValidationAbstract {

    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    protected function validate(Request $request): ?string
    {
        $route = $request->getAttribute('route');
        $code = $route->getArgument('code');

        $user = $this->userRepository->findOneByCode($code);
        return $user === null ? 'User does not exist' : null;
    }
}
