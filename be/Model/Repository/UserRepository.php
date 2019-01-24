<?php

namespace Model\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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
}