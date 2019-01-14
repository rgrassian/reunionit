<?php

namespace App\EventListener;

use App\Entity\Unavailability;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Toiba\FullCalendarBundle\Entity\Event;
use Toiba\FullCalendarBundle\Event\CalendarEvent;

class FullCalendarListener
{
    private $requestStack;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, UrlGeneratorInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->router = $router;
    }

    public function loadEvents(CalendarEvent $calendar)
    {
        $startDate = $calendar->getStart();
        $endDate = $calendar->getEnd();

        // Si un id est défini on affiche les events d'une seule salle,
        // sinon on affiche tous les events de toutes les salles.
        if (isset($calendar->getFilters()['id'])) {
            $roomId = $calendar->getFilters()['id'];
            $unavailabilities = $this->em->getRepository(Unavailability::class)
                ->findUnavailabilitiesByRoomByDates($roomId, $startDate, $endDate);
        } else {
            $unavailabilities = $this->em->getRepository(Unavailability::class)
                ->findUnavailabilitiesByDates($startDate, $endDate);
        }

        // On crée un event pour chaque unavailability.
        foreach($unavailabilities as $unavailability) {

            $eventTitle = $unavailability->getObject();

            // S'il s'agit du calendrier général, on affiche le nom des salles sur les events.
            if (!isset($calendar->getFilters()['id'])) {
                $eventTitle = 'Salle ' . $unavailability->getRoom()->getName() . ' | ' . $eventTitle;
            }

            // Chaque event prend trois arguments : titre, date de début, date de fin.
            $bookingEvent  = new Event(
                $eventTitle,
                $unavailability->getStartDate(),
                $unavailability->getEndDate()
            );

            if($unavailability->getStartDate()->format('H') === '08'
                && $unavailability->getEndDate()->format('H') === '20') {
                $bookingEvent->setAllDay(true);
            }

            // Création du lien vers l'unavailability sur l'event affiché par le calendrier.
            $bookingEvent->setUrl(
                $this->router->generate('unavailability_show', [
                    'id' => $unavailability->getId(),
                ])
            );

            // Si l'event couvre une ou plusieurs journées complète(s),
            // on reset les dates à 00h pour que le calendrier l'affiche dans la ligne "Toute la journée".
            if ($bookingEvent->isAllDay()) {
                $bookingEvent->setStartDate(\DateTime::createFromFormat('Y/m/d H:i:s', $bookingEvent
                        ->getStartDate()->format('Y/m/d') . ' 00:00:00'));
                $bookingEvent->setEndDate(\DateTime::createFromFormat('Y/m/d H:i:s', $bookingEvent
                        ->getEndDate()->format('Y/m/d') . ' 00:00:00')->modify('+1 day'));
            }

            // Ajout de l'évènement au calendrier
            $calendar->addEvent($bookingEvent);
        }

        // You may want to make a custom query to populate the calendar

//        $calendar->addEvent(new Event(
//            'Event 1',
//            new \DateTime('Tuesday this week'),
//            new \DateTime('Wednesdays this week')
//        ));
//
//        // If the end date is null or not defined, it creates a all day event
//        $calendar->addEvent(new Event(
//            'Event All day',
//            new \DateTime('Friday this week')
//        ));
    }
}