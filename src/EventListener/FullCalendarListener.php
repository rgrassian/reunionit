<?php

namespace App\EventListener;

use App\Entity\Unavailability;
use App\Repository\UnavailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Toiba\FullCalendarBundle\Entity\Event;
use Toiba\FullCalendarBundle\Event\CalendarEvent;

class FullCalendarListener
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    public function loadEvents(CalendarEvent $calendar)
    {
        $startDate = $calendar->getStart();
        $endDate = $calendar->getEnd();

        // Si un id est défini on affiche une seule salle, sinon on affiche tout
        if (isset($calendar->getFilters()['id'])) {
            $roomId = $calendar->getFilters()['id'];
            $unavailabilities = $this->em->getRepository(Unavailability::class)
                ->findUnavailabilitiesByRoomByDates($roomId, $startDate, $endDate);
        } else {
            $unavailabilities = $this->em->getRepository(Unavailability::class)
                ->findUnavailabilitiesByDates($startDate, $endDate);
        }

        foreach($unavailabilities as $unavailability) {

            // this create the events with your own entity (here booking entity) to po
            $bookingEvent  = new Event(
                $unavailability->getRoom()->getName(),
                $unavailability->getStartDate(),
                $unavailability->getEndDate() // If the end date is null or not defined, it creates an all day event
            );

            $bookingEvent->setUrl(
                $this->router->generate('unavailability_show', [
                    'id' => $unavailability->getId(),
                ])
            );
            // finally, add the booking to the CalendarEvent for displaying on the calendar
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