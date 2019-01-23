<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findActiveUsers()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.deletedAt is null')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    public function findLastOrganiser()
//    {
//        return $this->createQueryBuilder('u')
//            ->join('u.unavailabilities', 'un')
//            ->addSelect('COUNT(un) AS unavailabilities_count')
//            ->groupBy('u')
//            ->orderBy('unavailabilities_count', 'DESC')
//            ->setMaxResults(1)
//            ->getQuery()
//            ->getOneOrNullResult();
//    }

    public function findLastMonthOrganiser()
    {
        return $this->createQueryBuilder('u')
            ->join('u.unavailabilities', 'un')
            ->addSelect('COUNT(un) AS unavailabilities_count')
            ->groupBy('u')
            ->orderBy('unavailabilities_count', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastMonthGuest()
    {
        return $this->createQueryBuilder('u')
            ->join('u.invitations', 'i')
            ->addSelect('COUNT(i) AS invitations_count')
            ->groupBy('u')
            ->orderBy('invitations_count', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
