<?php

namespace Model\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    private $gameNid;

    /**
     * @ORM\Column(type="integer", name="winner_rating_before")
     * @var int
     */
    private $winnerRatingBefore;

    /**
     * @ORM\Column(type="integer", name="looser_rating_before")
     * @var int
     */
    private $looserRatingBefore;

    /**
     * @ORM\Column(type="integer", name="rating_diff")
     * @var int
     */
    private $ratingDiff;

    /**
     * @ORM\Column(type="datetime", name="cdate")
     * @var \DateTime
     */
    private $cdate;

    /// RELACJE !!! ///

    /**
     * @ORM\Column(type="integer", name="winner_user_nid")
     * @var int
     */
    private $winnerUserNid;

    /**
     * @ORM\Column(type="integer", name="looser_user_nid")
     * @var int
     */
    private $looserUserNid;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="wonGameList")
     * @ORM\JoinColumn(name="winner_user_nid", referencedColumnName="user_nid")
     * @var User
     */
    private $winnerUser;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="lostGameList")
     * @ORM\JoinColumn(name="looser_user_nid", referencedColumnName="user_nid")
     * @var User
     */
    private $looserUser;

    public function __constructor()
    {
        $this->cdate = new \DateTime();
    }

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
