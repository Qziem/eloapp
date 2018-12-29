<?php
namespace Model\Repository;

// use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Model\Entity\User;

class UserRepository extends EntityRepository {
  public function findUserByCode(string $code): ?User {
    $users = $this->createQueryBuilder('u')
      ->where('u.code = :code')
      ->setParameter('code', $code)
      ->getQuery()
      ->setMaxResults(1)
      ->getResult();

    return isset($users[0]) ? $users[0] : null ;
  }
}