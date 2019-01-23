<?php

namespace Service;

class RatingCalculator
{
    public function calcNewRatingDiff(
        int $oldWinnerRating,
        int $oldLooserRating
    ): int {
        $kfactor = 32;

        $winnerLooserDiff = $oldLooserRating - $oldWinnerRating;
        $pWinner = 1 / (1 + (10 ** ($winnerLooserDiff / 400))); // probability of winning winner user

        return round($kfactor * (1 - $pWinner));
    }
}