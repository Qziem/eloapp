<?php

namespace GraphQLElo\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class UsersType extends ObjectType {
    public function __construct() {
        $config = [
            'fields' => [
                'code' => [
                    'type' => Type::string(),
                    'resolve' => function ($root) { return $root . "bb"; },
                ]
            ],

        ];
        parent::__construct($config);
    }

}
