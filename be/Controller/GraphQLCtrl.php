<?php

namespace Controller;

use Slim\Http\Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQLElo\Resolvers\UsersResolver;
use GraphQLElo\Types\UsersType;

class GraphQLCtrl {
    /** @var UsersResolver */
    private $usersResolver;
    
    /** @var UsersType */
    private $usersType;

    public function __construct(UsersResolver $usersResolver, UsersType $usersType) {
        $this->usersResolver = $usersResolver;
        $this->usersType = $usersType;
    }

    public function api(Request $request, Response $response): Response {
        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => $this->getQueryFields(),
            ]),
        ]);

        $rawInput = $request->getBody();
        $input = json_decode($rawInput, true);
        $query = $input['query'];
        $variableValues = isset($input['variables']) ? $input['variables'] : null;
        
        try {
            $result = GraphQL::executeQuery($schema, $query, null, null, $variableValues);
            $output = $result->toArray();
        } catch (\Exception $e) {
            $output = [
                'errors' => [
                    [
                        'message' => $e->getMessage()
                    ]
                ]
            ];
        }
        
        return $response->withJson($output);
    }
    
    private function getQueryFields() {
        return [
            'users' => [
                'type' => $this->usersType,
                'args' => [
                    'message' => Type::nonNull(Type::string()),
                ],
                'resolve' => $this->usersResolver,
            ],
        ];
    }
}
    