<?php

namespace Middleware\Validation;

use \Psr\Http\Message\ServerRequestInterface as Request;
use Model\Repository\GameRepository;
use Model\Repository\UserRepository;
use Service\RemoveGameSvc;

class ValidationRemoveGame extends ValidationAbstract {

    /** @var GameRepository */
    private $gameRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var RemoveGameSvc */
    private $removeGameSvc;

    public function __construct(
        GameRepository $gameRepository,
        UserRepository $userRepository,
        RemoveGameSvc $removeGameSvc
    ) {
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;
        $this->removeGameSvc = $removeGameSvc;
    }

    protected function validate(Request $request): ?string
    {
        $route = $request->getAttribute('route');
        $code = $route->getArgument('code');

        $user = $this->userRepository->findOneByCode($code);
        if ($user === null) {
            return 'Player does not exist';
        }

        $userNid = $user->getUserNid();

        $lastGame = $this->gameRepository->findLastGame($userNid);
        if ($lastGame === null) {
            return 'Player has no games';
        }

        $opponentUser = $this->removeGameSvc->getOpponentFromGame($lastGame, $userNid);
        $opponentLastGame = $this->gameRepository->requireLastGame($opponentUser->getUserNid());

        if ($lastGame->getGameNid() !== $opponentLastGame->getGameNid()) {
            return 'Opponent has later games';
        }

        return null;
    }

}
