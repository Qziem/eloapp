<?php

namespace Model\Repository;

use Doctrine\ORM\EntityRepository;
use Model\Entity\User;

class UserRepository extends EntityRepository
{
    public function findOneByCode(string $code): ?User
    {
        $result = $this->createQueryBuilder('u')
            ->where('u.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getResult();

        return $result !== null ? $result[0] : null;
    }
    
    public function requireUserByCode(string $code): User {
        $user = $this->findOneByCode($code);
        if ($user === null) {
            throw new \Error('User with code ' . $code . ' - does not exist in database');
        }

        return $user;
    }

}