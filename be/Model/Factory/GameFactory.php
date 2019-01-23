<?php

namespace Model\Factory;

use Model\Entity\User;
use Model\Entity\Game;
use Service\RatingCalculator;

class GameFactory
{
    /** @var RatingCalculator */
    private $ratingCalculator;

    public function __construct(RatingCalculator $ratingCalculator)
    {
        $this->ratingCalculator = $ratingCalculator;
    }

    public function createGameEntity(
        User $winnerUser,
        User $looserUser
    ): Game {
        $oldWinnerRating = $winnerUser->getRating();
        $oldLooserRating = $looserUser->getRating();

        $ratingDiff = $this->ratingCalculator->calcNewRatingDiff($oldWinnerRating, $oldLooserRating);

        $game = new Game();
        $game->setWinnerUser($winnerUser);
        $game->setLooserUser($looserUser);
        $game->setWinnerRatingBefore($oldWinnerRating);
        $game->setLooserRatingBefore($oldLooserRating);
        $game->setRatingDiff($ratingDiff);

        return $game;
    }
}