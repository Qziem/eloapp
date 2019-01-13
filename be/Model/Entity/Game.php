<?php

namespace Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Model\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Model\Repository\GameRepository")
 * @ORM\Table(name="games")
 */
class Game
{
    /**
     * @ORM\Column(type="integer", name="game_nid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $gameNid;

    /**
     * @ORM\Column(type="integer", name="winner_rating_before")
     * @var int
     */
    protected $winnerRatingBefore;

    /**
     * @ORM\Column(type="integer", name="looser_rating_before")
     * @var int
     */
    protected $looserRatingBefore;

    /**
     * @ORM\Column(type="integer", name="rating_diff")
     * @var int
     */
    protected $ratingDiff;

    /**
     * @ORM\Column(type="datetime", name="cdate")
     * @var \DateTime
     */
    protected $cdate;

    /// RELACJE !!! ///

    /**
     * @ORM\Column(type="integer", name="winner_user_nid")
     * @var int
     */
    protected $winnerUserNid;

    /**
     * @ORM\Column(type="integer", name="looser_user_nid")
     * @var int
     */
    protected $looserUserNid;

    /**
     * @ORM\ManyToOne(targetEntity="Model\Entity\User", inversedBy="winGameList")
     * @ORM\JoinColumn(name="winner_user_nid", referencedColumnName="user_nid")
     * @var User
     */
    protected $winnerUser;

    /**
     * @ORM\ManyToOne(targetEntity="Model\Entity\User", inversedBy="looseGameList")
     * @ORM\JoinColumn(name="looser_user_nid", referencedColumnName="user_nid")
     * @var User
     */
    protected $looserUser;

    public function getGameNid(): int
    {
        return $this->gameNid;
    }

    public function setGameNid(int $gameNid): void
    {
        $this->gameNid = $gameNid;
    }

    public function getWinnerRatingBefore(): int
    {
        return $this->winnerRatingBefore;
    }

    public function setWinnerRatingBefore(int $winnerRatingBefore): void
    {
        $this->winnerRatingBefore = $winnerRatingBefore;
    }

    public function getLooserRatingBefore(): int
    {
        return $this->looserRatingBefore;
    }

    public function setLooserRatingBefore(int $looserRatingBefore): void
    {
        $this->looserRatingBefore = $looserRatingBefore;
    }

    public function getRatingDiff(): int
    {
        return $this->ratingDiff;
    }

    public function setRatingDiff(int $ratingDiff): void
    {
        $this->ratingDiff = $ratingDiff;
    }

    public function getCdate(): \DateTime
    {
        return $this->cdate;
    }

    public function setCdate(\DateTime $cdate): void
    {
        $this->cdate = $cdate;
    }

    public function getWinnerUser(): User
    {
        return $this->winnerUser;
    }

    public function setWinnerUser(User $winnerUser): void
    {
        $this->winnerUser = $winnerUser;
    }

    public function getLooserUser(): User
    {
        return $this->looserUser;
    }

    public function setLooserUser(User $looserUser): void
    {
        $this->looserUser = $looserUser;
    }
}
