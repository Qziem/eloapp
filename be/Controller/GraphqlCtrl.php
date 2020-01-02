<?php

namespace Controller;

use Slim\Http\Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQLElo\Resolvers\UsersResolver;
use GraphQLElo\Types\UserType;

class GraphqlCtrl {
    /** @var UsersResolver */
    private $usersResolver;
    
    /** @var UserType */
    private $userType;

    public function __construct(UsersResolver $usersResolver, UserType $userType) {
        $this->usersResolver = $usersResolver;
        $this->userType = $userType;
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
        $variableValues = $input['variables'] ?? null;
        
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
    
    private function getQueryFields(): array {
        return [
            'users' => [
                'type' => Type::nonNull(Type::listOf(Type::nonNull($this->userType))),
                'resolve' => $this->usersResolver,
            ],
        ];
    }
}
    