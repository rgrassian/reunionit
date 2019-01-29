<?php
/**
 * Created by PhpStorm.
 * User: Etudiant
 * Date: 29/01/2019
 * Time: 11:11
 */

namespace App\Service;


use App\Entity\Unavailability;
use Doctrine\ORM\EntityManagerInterface;

class UnavailabilityManager
{
    private $entityManager;

    /**
     * UnavailabilityManager constructor.
     * @param $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Supprime une Unavailability de la BDD.
     * @param Unavailability $unavailability
     */
    public function removeUnavailabilityFromDatabase(Unavailability $unavailability)
    {
        $entityManager = $this->entityManager;
        $entityManager->remove($unavailability);
        $entityManager->flush();
    }
}