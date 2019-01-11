<?php

namespace App\Validator;

use App\Repository\UnavailabilityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AvailabilityValidator extends ConstraintValidator
{
    private $unavailabilityRepository;

    /**
     * AvailabilityValidator constructor.
     * @param UnavailabilityRepository $unavailabilityRepository
     */
    public function __construct(UnavailabilityRepository $unavailabilityRepository)
    {
        $this->unavailabilityRepository = $unavailabilityRepository;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($this->availability($value)) {
            $violation = $constraint->availabilityMessage;
        }

        if ($this->endAfterStart($value)) {
            $violation = $constraint->endAfterStartMessage;
        }

        if ($this->pastDates($value)) {
            $violation = $constraint->pastDatesMessage;
        }

        if ($this->weekEndDates($value)) {
            $violation = $constraint->weekEndDatesMessage;
        }
        $this->setViolation($violation ?? null);
    }

    public function setViolation($violation)
    {
        $this->context->buildViolation($violation)
            ->addViolation();
    }

    public function availability($value)
    {
        $unavailabilities = $this->unavailabilityRepository->findUpcomingUnavailabilitiesByRoom($value->getRoom());
        dd($unavailabilities);
        foreach ($unavailabilities as $unavailability) {
            if ($unavailability->getStartDate() < $value->getStartDate() && $value->getStartDate() < $unavailability->getEndDate()) {
                return true;
            }
            if ($unavailability->getStartDate() < $value->getEndDate() && $value->getEndDate() < $unavailability->getEndDate()) {
                return true;
            }
            if ($value->getStartDate() < $unavailability->getStartDate() && $unavailability->getStartDate() < $value->getEndDate()) {
                return true;
            }
            if ($value->getStartDate() < $unavailability->getEndDate() && $unavailability->getStartDate() < $value->getEndDate()) {
                return true;
            }
        }
        return false;
    }

    public function endAfterStart($value)
    {
        if ($value->getEndDate() < $value->getStartDate()) {
            return true;
        }
        return false;
    }

    public function pastDates($value)
    {
        $now = new \DateTime();
        if ($value->getStartDate() < $now) {
            return true;
        }
        return false;
    }

    public function weekEndDates($value)
    {
        if ($this->isWeekEndDate($value->getStartDate()) || $this->isWeekEndDate($value->getEndDate())) {
            return true;
        }
        return false;
    }

    public function isWeekEndDate(\DateTime $date) : bool
    {
        $day = $date->format('w');
        return $day == 0 || $day == 6;
    }
}
