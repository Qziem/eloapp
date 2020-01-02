<?php

namespace GraphQLElo\Types;

use Model\Entity\User;
use Service\UsersSvc;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class UserType extends ObjectType {
    public function __construct(UsersSvc $usersSvc) {
        $config = [
            'fields' => [
                'userNid' => [
                    'type' => Type::nonNull(Type::int()),
                    'resolve' => static function (User $user) {
                        return $user->getUserNid();
                    },
                ],
                'code' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static function (User $user) {
                        return $user->getCode();
                    },
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static function (User $user) {
                        return $user->getName();
                    },
                ],
                'rating' => [
                    'type' => Type::nonNull(Type::int()),
                    'resolve' => static function (User $user) {
                        return $user->getRating();
                    },
                ],
                'team' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static function (User $user) {
                        return $user->getTeam();
                    },
                ],
                'trendRatingDiff' => [
                    'type' => Type::nonNull(Type::int()),
                    'resolve' => static function (User $user) use ($usersSvc) {
                        return $usersSvc->calculateTrendRatingDiffForGql($user);
                    },
                ],
            ],
        ];
        parent::__construct($config);
    }

}
