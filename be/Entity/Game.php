<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Repository\GameRepository")
 * @ORM\Table(name="games")
 */
class Game
{
  /**
   * @ORM\Column(type="integer", name="game_nid")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $gameNid;

  /**
   * @ORM\Column(type="integer", name="winner_rating_before")
   */
  protected $winnerRatingBefore;

  /**
   * @ORM\Column(type="integer", name="looser_rating_before")
   */
  protected $looserRatingBefore;

  /**
   * @ORM\Column(type="integer", name="rating_diff")
   */
	protected $ratingDiff;
	
	/**
   * @ORM\Column(type="datetime", name="cdate")
   */
  protected $cdate;

	/// RELACJE !!! ///

  /**
   * @ORM\Column(type="integer", name="winner_user_nid")
   */
  protected $winnerUserNid;

  /**
   * @ORM\Column(type="integer", name="looser_user_nid")
   */
  protected $looserUserNid;

	/**
    * @ORM\ManyToOne(targetEntity="Entity\User", inversedBy="games")
    * @ORM\JoinColumn(name="winner_user_nid", referencedColumnName="user_nid")
    */
	protected $winnerUser;

	/**
    * @ORM\ManyToOne(targetEntity="Entity\User", inversedBy="games")
    * @ORM\JoinColumn(name="looser_user_nid", referencedColumnName="user_nid")
    */
	protected $looserUser;  

  public function getGameNid(): int {
		return $this->gameNid;
	}

	public function setGameNid(int $gameNid): void {
		$this->gameNid = $gameNid;
	}

	public function getWinnerRatingBefore(): int {
		return $this->winnerRatingBefore;
	}

	public function setWinnerRatingBefore(int $winnerRatingBefore): void {
		$this->winnerRatingBefore = $winnerRatingBefore;
	}

	public function getLooserRatingBefore(): int {
		return $this->looserRatingBefore;
	}

	public function setLooserRatingBefore(int $looserRatingBefore): void {
		$this->looserRatingBefore = $looserRatingBefore;
	}

	public function getRatingDiff(): int {
		return $this->ratingDiff;
	}

	public function setRatingDiff(int $ratingDiff): void {
		$this->ratingDiff = $ratingDiff;
	}

	public function getCdate(): \DateTime {
		return $this->cdate;
	}

	public function setCdate(\DateTime $cdate): void {
		$this->cdate = $cdate;
	}

	public function getWinnerUser(): User {
		return $this->winnerUser;
	}

	public function setWinnerUser(User $winnerUser){
		$this->winnerUser = $winnerUser;
	}

	public function getLooserUser(): User {
		return $this->looserUser;
	}

	public function setLooserUser(User $looserUser) {
		$this->looserUser = $looserUser;
	}
}