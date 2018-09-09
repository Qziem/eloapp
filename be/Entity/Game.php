<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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

    public function getGameNid(){
		return $this->gameNid;
	}

	public function setGameNid($gameNid){
		$this->gameNid = $gameNid;
	}

	public function getWinnerRatingBefore(){
		return $this->winnerRatingBefore;
	}

	public function setWinnerRatingBefore($winnerRatingBefore){
		$this->winnerRatingBefore = $winnerRatingBefore;
	}

	public function getLooserRatingBefore(){
		return $this->looserRatingBefore;
	}

	public function setLooserRatingBefore($looserRatingBefore){
		$this->looserRatingBefore = $looserRatingBefore;
	}

	public function getRatingDiff(){
		return $this->ratingDiff;
	}

	public function setRatingDiff($ratingDiff){
		$this->ratingDiff = $ratingDiff;
	}

	public function getCdate(){
		return $this->cdate;
	}

	public function setCdate($cdate){
		$this->cdate = $cdate;
	}

	public function getWinnerUser(){
		return $this->winnerUser;
	}

	public function setWinnerUser($winnerUser){
		$this->winnerUser = $winnerUser;
	}

	public function getLooserUser(){
		return $this->looserUser;
	}

	public function setLooserUser($looserUser){
		$this->looserUser = $looserUser;
	}
}