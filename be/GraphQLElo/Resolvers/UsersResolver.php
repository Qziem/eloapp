<?php

namespace GraphQLElo\Resolvers;

class UsersResolver {
    public function __invoke($root, $args): String {
        return $args['message'];
    }
}
        