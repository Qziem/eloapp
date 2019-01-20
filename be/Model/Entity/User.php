<?php

namespace Model\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Model\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @ORM\Column(type="integer", name="user_nid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $userNid;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $code;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $team = '';

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $rating;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $deleted;

    /**
     * @ORM\OneToMany(targetEntity="Game", mappedBy="winnerUser")
     * @var Collection
     */
    private $wonGameList;

    /**
     * @ORM\OneToMany(targetEntity="Game", mappedBy="looserUser")
     * @var Collection
     */
    private $lostGameList;

    public function __construct()
    {
        $this->wonGameList = new ArrayCollection();
        $this->lostGameList = new ArrayCollection();
    }

    public function getUserNid(): int
    {
        return $this->userNid;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getTeam(): string
    {
        return $this->team;
    }

    public function setTeam(string $team): void
    {
        $this->team = $team;
    }

    public function getWonGameList(): Collection
    {
        return $this->wonGameList;
    }

    public function getLostGameList(): Collection
    {
        return $this->lostGameList;
    }

    public function getLastWonGameList(): Collection
    {
        return $this->filterLastGameList($this->wonGameList);
    }

    public function getLastLostGameList(): Collection
    {
        return $this->filterLastGameList($this->lostGameList);
    }

    private function filterLastGameList(Collection $gameList): Collection
    {
        $sliceDays = 7;
        $fromDate = new \DateTime();
        $fromDate->sub(new \DateInterval('P' . $sliceDays . 'D'));
        $fromDate->setTime(0, 0);

        $criteria = new Criteria();
        $expr = new Comparison('cdate', '>', $fromDate);
        $criteria->where(new Comparison('cdate', '>', $fromDate));

        return $gameList->matching($criteria); 
    }
}
