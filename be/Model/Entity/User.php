<?php
namespace Model\Entity;

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
   */
  protected $userNid;

  /**
   * @ORM\Column(type="string")
   */
  protected $code;

  /**
   * @ORM\Column(type="string")
   */
  protected $name;

  /**
   * @ORM\Column(type="integer")
   */
  protected $rating;

  /**
   * @ORM\Column(type="integer")
   */
  protected $deleted;

  public function toArray(): array {
    return [
        'userNid' => $this->userNid,
        'code' => $this->code,
        'name' => $this->name,
        'rating' => $this->rating,
    ];
  }

  public function getUserNid(): int {
    return $this->userNid;
  }

  public function getCode(): string {
    return $this->code;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getRating(): int {
    return $this->rating;
  }

  public function getDeleted(): bool {
    return $this->deleted;
  }

  public function setUserNid(int $userNid): void {
    $this->userNid = $userNid;
  }

  public function setCode(string $code): void {
    $this->code = $code;
  }

  public function setName(string $name): void {
    $this->name = $name;
  }

  public function setRating(int $rating): void {
    $this->rating = $rating;
  }

  public function setDeleted(bool $deleted): void {
    $this->deleted = $deleted;
  }
}