<?php

namespace GraphQLElo\Resolvers;

use Service\UsersSvc;

class UsersResolver {
    /** @var UsersSvc */
    private $usersSvc;

    public function __construct(UsersSvc $usersSvc)
    {
        $this->usersSvc = $usersSvc;
    }

    public function __invoke(): array
    {
        return $this->usersSvc->getUsers();
    }
}
        