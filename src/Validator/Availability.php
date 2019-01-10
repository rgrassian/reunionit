<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Availability extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $availabilityMessage = 'La période est indisponible, veuillez vérifier le calendrier.';
    public $endAfterStartMessage = 'La réunion ne peut pas finir avant d\'avoir commencé. Malheureusement.';

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
