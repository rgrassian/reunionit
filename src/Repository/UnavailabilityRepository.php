<?php

namespace App\Repository;

use App\Entity\Room;
use App\Entity\Unavailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Unavailability|null find($id, $lockMode = null, $lockVersion = null)
 * @method Unavailability|null findOneBy(array $criteria, array $orderBy = null)
 * @method Unavailability[]    findAll()
 * @method Unavailability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnavailabilityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Unavailability::class);
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

    public function findAllAndOrder()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByOrganiserAndOrder($organiser)
    {
        return $this->createQueryBuilder('u')
            ->join('u.room', 'r')
            ->addSelect('r')
            ->where('u.organiser = :organiser')
            ->setParameter('organiser', $organiser)
            ->orderBy('u.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByGuestAndOrder($guest)
    {
        return $this->createQueryBuilder('u')
            ->join('u.guests', 'g')
            ->join('u.room', 'r')
            ->addSelect('r')
            ->where('g = :guest')
            ->setParameter('guest', $guest)
            ->orderBy('u.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function findUnavailabilitiesByRoomByDates($roomId, $startDate, $endDate)
    {
        return $this->createQueryBuilder('u')
            ->where('u.room = :room_id')
            ->setParameter('room_id', $roomId)
            ->andWhere('u.startDate BETWEEN :startDate and :endDate')
            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }

    public function findUnavailabilitiesByDates($startDate, $endDate)
    {
        return $this->createQueryBuilder('u')
            ->where('u.startDate BETWEEN :startDate and :endDate')
            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }

    public function findUnavailabilitiesByRoom($roomId)
    {
        return $this->createQueryBuilder('u')
            ->where('u.room = :room_id')
            ->setParameter('room_id', $roomId)
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingUnavailabilitiesByRoom($roomId)
    {
        return $this->createQueryBuilder('u')
            ->where('u.room = :room_id')
            ->setParameter('room_id', $roomId)
            ->andWhere('u.startDate > :now')
            ->setParameter('now', (new \DateTime())->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }
}
