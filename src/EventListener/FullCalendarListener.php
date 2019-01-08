<?php

namespace App\EventListener;

use App\Entity\Unavailability;
use App\Repository\UnavailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Toiba\FullCalendarBundle\Entity\Event;
use Toiba\FullCalendarBundle\Event\CalendarEvent;

class FullCalendarListener
{
    /**
     * @var EntityManagerInterface
     */
    private $unavailabilityRepository;

    public function __construct(UnavailabilityRepository $unavailabilityRepository)
    {
        $this->unavailabilityRepository = $unavailabilityRepository;
    }

    public function loadEvents(CalendarEvent $calendar)
    {
        $startDate = $calendar->getStart();
        $endDate = $calendar->getEnd();
        $roomId = $calendar->getFilters()['id'];

        $unavailabilities = $this->unavailabilityRepository
            ->findUnavailabilitiesByRoomByDates($roomId, $startDate, $endDate);

        foreach($unavailabilities as $unavailability) {

            // this create the events with your own entity (here booking entity) to po
            $bookingEvent  = new Event(
                $unavailability->getRoom()->getName(),
                $unavailability->getStartDate(),
                $unavailability->getEndDate() // If the end date is null or not defined, it creates a all day event
            );

            // finally, add the booking to the CalendarEvent for displaying on the calendar
            $calendar->addEvent($bookingEvent);
        }

        // You may want to make a custom query to populate the calendar

        $calendar->addEvent(new Event(
            'Event 1',
            new \DateTime('Tuesday this week'),
            new \DateTime('Wednesdays this week')
        ));

        // If the end date is null or not defined, it creates a all day event
        $calendar->addEvent(new Event(
            'Event All day',
            new \DateTime('Friday this week')
        ));
    }
}