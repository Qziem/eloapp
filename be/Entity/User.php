<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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

    public function toArray() {
        return [
            'userNid' => $this->userNid,
            'code' => $this->code,
            'name' => $this->name,
            'rating' => $this->rating,
        ];
    }

    public function getUserNid() {
        return $this->userNid;
    }

    public function getCode() {
        return $this->code;
    }

    public function getName() {
        return $this->name;
    }

    public function getRating() {
        return $this->rating;
    }

    public function getDeleted() {
      return $this->deleted;
    }

    public function setUserNid($userNid) {
        $this->userNid = $userNid;
        return $this;
    }

    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }
 
    public function setRating($rating) {
        $this->rating = $rating;
        return $this;
    }

    public function setDeleted($deleted) {
      $this->deleted = $deleted;
      return $this;
    }
}