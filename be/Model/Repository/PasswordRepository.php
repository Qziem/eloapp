<?php

namespace Model\Repository;

use Doctrine\ORM\EntityRepository;

class PasswordRepository extends EntityRepository
{
    public function findPassword(): string {
        $passwordEntity = $this->createQueryBuilder('p')
            ->getQuery()
            ->getSingleResult();
        
        return $passwordEntity->getPassword();
    }
}
