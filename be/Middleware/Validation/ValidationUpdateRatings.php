<?php

namespace Middleware\Validation;

use \Psr\Http\Message\ServerRequestInterface as Request;
use Model\Repository\UserRepository;

class ValidationUpdateRatings extends ValidationAbstract {

    /** @var UserRepository */
    private $userRepository;

    function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    protected function validate(Request $request): ?string
    {
        $json = $request->getBody();
        $usersCodes = json_decode($json, true);
        $winnerUserCode = $usersCodes['winnerUserCode'];
        $looserUserCode = $usersCodes['looserUserCode'];

        $winnerUser = $this->userRepository->findOneByCode($winnerUserCode);
        $looserUser = $this->userRepository->findOneByCode($looserUserCode);

        if ($winnerUser === null && $looserUser === null) {
            return "Winner and looser does not exist";
        }
        if ($winnerUser === null) {
            return "Winner does not exist";
        }
        if ($looserUser === null) {
            return "Looser does not exist";
        }
        if ($winnerUser->getUserNid() === $looserUser->getUserNid()) {
            return "Winner is same as looser";
        }

        return null;
    }

}
