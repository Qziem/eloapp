<?php

namespace Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Model\Repository\PasswordRepository")
 * @ORM\Table(name="password")
 */
class Password
{
    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     * @var string
     */
    private $password;

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

}
