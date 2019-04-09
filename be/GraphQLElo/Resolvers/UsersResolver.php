<?php

namespace GraphQLElo\Resolvers;

use Model\Entity\User;
use Model\Repository\UserRepository;

class UsersResolver {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @return User[];
     */
    public function __invoke(): array
    {
        return $this->userRepository->findBy(
            ['deleted' => 0],
            ['rating' => 'DESC', 'code' => 'ASC']
        );
    }
}
        