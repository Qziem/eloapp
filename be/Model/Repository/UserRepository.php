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

    /**
     * @param string $code
     * @return User
     * @throws \Doctrine\ORM\NoResultException
     * @TODO ta metoda będzie wywalona w issuesie: #14
     */
    public function requireUserByCode(string $code): User
    {
        try {
            return $this->createQueryBuilder('u')
                ->where('u.code = :code')
                ->setParameter('code', $code)
                ->getQuery()
                ->getSingleResult();
        } catch (NonUniqueResultException $e) {
            throw new \Error('User codes are not unique!');
        }
    }
}