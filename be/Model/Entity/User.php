<?php

namespace Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\Column(type="integer")
     * @var int
     */
    private $deleted;

    /**
     * @ORM\OneToMany(targetEntity="Model\Entity\Game", mappedBy="winnerUser")
     * @var ArrayCollection
     */
    private $winGameList;

    /**
     * @ORM\OneToMany(targetEntity="Model\Entity\Game", mappedBy="looserUser")
     * @var ArrayCollection
     */
    private $looseGameList;

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

    public function getWinGameList(): array
    {
        return $this->winGameList->toArray();
    }

    public function setWinGameList(array $winGameList): void
    {
        $this->winGameList = new ArrayCollection($winGameList);
    }

    public function getLooseGameList(): array
    {
        return $this->looseGameList->toArray();
    }

    public function setLooseGameList(array $looseGameList): void
    {
        $this->looseGameList = new ArrayCollection($looseGameList);
    }
}
