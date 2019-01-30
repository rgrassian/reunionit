<?php
/**
 * Created by PhpStorm.
 * User: Etudiant
 * Date: 29/01/2019
 * Time: 11:11
 */

namespace App\Service;

use App\Service\EmailManager;
use App\Entity\Room;
use App\Entity\Unavailability;
use App\Entity\User;
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

    /**
     * Supprime toutes les réunions à venir organisées par un User.
     * @param \App\Service\EmailManager $emailManager
     * @param User $organiser
     * @param \Swift_Mailer $mailer
     */
    public function deleteUpcomingUnavailabilitiesByOrganiser(EmailManager $emailManager, User $organiser, \Swift_Mailer $mailer)
    {
        $unavailabilityRepository = $this->entityManager->getRepository(Unavailability::class);

        $unavailabilities = $unavailabilityRepository->findUpcomingUnavailabilitiesByOrganiser($organiser);

        foreach ($unavailabilities as $unavailability) {
            // Envoi de mails aux invités
            $guests = $unavailability->getGuests();
            foreach ($guests as $guest) {
                $emailManager->sendEmail($mailer,
                    'ReunionIT | Annulation d\'une invitation',
                    $guest->getEmail(),
                    'email/unavailability_delete_guest.html.twig',
                    ['guest'=>$guest,'data' => $unavailability]
                );
            }

            $this->removeUnavailabilityFromDatabase($unavailability);
        }
    }

    /**
     * Supprime toutes les réunions à venir organisées dans une salle.
     * @param \App\Service\EmailManager $emailManager
     * @param Room $room
     * @param \Swift_Mailer $mailer
     */
    public function deleteUpcomingUnavailabilitiesByRoom(EmailManager $emailManager, Room $room, \Swift_Mailer $mailer)
    {
        $unavailabilityRepository = $this->entityManager->getRepository(Unavailability::class);

        $unavailabilities = $unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($room);

        foreach ($unavailabilities as $unavailability) {
            // Envoi de mails aux invités
            $guests = $unavailability->getGuests();
            foreach ($guests as $guest) {
                $emailManager->sendEmail($mailer,
                    'ReunionIT | Annulation d\'une invitation',
                    $guest->getEmail(),
                    'email/unavailability_delete_guest.html.twig',
                    ['guest'=>$guest,'data' => $unavailability]
                );
            }

            $this->removeUnavailabilityFromDatabase($unavailability);
        }
    }


}