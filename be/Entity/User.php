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
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $user_nid;

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

    public function toArray() {
        return [
            'user_nid' => $this->user_nid,
            'code' => $this->code,
            'name' => $this->name,
            'rating' => $this->rating,
        ];
    }

    public function getUser_nid() {
        return $this->column1;
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

    public function setUser_nid($user_nid) {
        $this->user_nid = $user_nid;
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
}