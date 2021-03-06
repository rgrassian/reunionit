<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    // Permet d'injecter l'utilisateur courant.
    private $security;

    public function __construct(RegistryInterface $registry,
                                Security $security)
    {
        parent::__construct($registry, User::class);
        $this->security = $security;
    }

    public function findActiveUsers()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveUsersExceptCurrent()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.id != :user_id')
            ->andWhere('u.deletedAt is null')
            ->setParameter('user_id', $this->security->getUser()->getId())
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLastMonthOrganiser()
    {
        $lastMonthEndDate = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/m/01 00:00:00'));
        $m = ($lastMonthEndDate->format('n') - 1) % 12;
        $lastMonthStartDate = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m.'/01 00:00:00'));

        return $this->createQueryBuilder('u')
            ->join('u.unavailabilities', 'un')
            ->andWhere('un.startDate BETWEEN :lastMonthStartDate and :lastMonthEndDate')
            ->setParameter('lastMonthStartDate', $lastMonthStartDate)
            ->setParameter('lastMonthEndDate', $lastMonthEndDate)
            ->addSelect('COUNT(un) AS unavailabilities_count')
            ->groupBy('u')
            ->orderBy('unavailabilities_count', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()[0];
    }

    public function findLastMonthMostInvited()
    {
        $lastMonthEndDate = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/m/01 00:00:00'));
        $m = ($lastMonthEndDate->format('n') - 1) % 12;
        // Si $m = 0, le new Datetime prendra la valeur 12 pour le mois.
        $lastMonthStartDate = \DateTime::createFromFormat('Y/m/d H:i:s', (new \Datetime())->format('Y/'.$m.'/01 00:00:00'));

        return $this->createQueryBuilder('u')
            ->where('u.roles NOT LIKE :roles')
            ->setParameter('roles', '%ROLE_GUEST%')
            ->join('u.invitations', 'i')
            ->andWhere('i.startDate BETWEEN :lastMonthStartDate and :lastMonthEndDate')
            ->setParameter('lastMonthStartDate', $lastMonthStartDate)
            ->setParameter('lastMonthEndDate', $lastMonthEndDate)
            ->addSelect('COUNT(i) AS invitations_count')
            ->groupBy('u')
            ->orderBy('invitations_count', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()[0];
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
