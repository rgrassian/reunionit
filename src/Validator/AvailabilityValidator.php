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
        /* @var $constraint App\Validator\Availability */

        if($this->availability($value)) {
            $this->context->buildViolation($constraint->availabilityMessage)
                ->addViolation();
        }

        if($this->endAfterStart($value)) {
            $this->context->buildViolation($constraint->endAfterStartMessage)
                ->addViolation();
        }

    }

    public function availability($value)
    {
        $unavailabilities = $this->unavailabilityRepository->findUnavailabilitiesByRoom($value->getRoom());

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
            //return false;
        }
    }

    public function endAfterStart($value)
    {
        if ($value->getEndDate() < $value->getStartDate()) {
            return true;
        }
    }
}
