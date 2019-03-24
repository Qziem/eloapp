<?php

namespace GraphQLElo\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class UsersType extends ObjectType {
    public function __construct() {
        $config = [
            'fields' => [
                'userNid' => [
                    'type' => Type::int(),
                    'resolve' => function ($root) {
                        return $root['userNid'];
                    },
                ],
                'code' => [
                    'type' => Type::string(),
                    'resolve' => function ($root) {
                        return $root['code'];
                    },
                ],
                'name' => [
                    'type' => Type::string(),
                    'resolve' => function ($root) {
                        return $root['name'];
                    },
                ],
                'rating' => [
                    'type' => Type::string(),
                    'resolve' => function ($root) {
                        return $root['code'];
                    },
                ],
                'team' => [
                    'type' => Type::string(),
                    'resolve' => function ($root) {
                        return $root['code'];
                    },
                ],
                'trendRatingDiff' => [
                    'type' => Type::string(),
                    'resolve' => function ($root) {
                        return $root['code'];
                    },
                ],
            ],
        ];
        parent::__construct($config);
    }

}
