<?php

namespace App\Repository;

use App\Entity\Occupied;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Occupied|null find($id, $lockMode = null, $lockVersion = null)
 * @method Occupied|null findOneBy(array $criteria, array $orderBy = null)
 * @method Occupied[]    findAll()
 * @method Occupied[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OccupiedRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Occupied::class);
    }

    // /**
    //  * @return Occupied[] Returns an array of Occupied objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Occupied
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
