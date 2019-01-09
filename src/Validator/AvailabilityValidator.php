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
     */
    public function __construct(UnavailabilityRepository $unavailabilityRepository)
    {
        $this->unavailabilityRepository = $unavailabilityRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint App\Validator\Availability */

        if($this->valid($value)) {
            $this->context->buildViolation($constraint->message)
                //->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }

    public function valid($value)
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
}
