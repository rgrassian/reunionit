<?php

namespace App\Service;

use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class EmailManager extends AbstractController
{
    /**
     * @param Swift_Mailer $mailer
     * @param $object
     * @param $to
     * @param $view
     * @param $options
     */
    public function sendEmail(Swift_Mailer $mailer, $object, $to, $view, $options)
    {
        $message = (new \Swift_Message($object))
            ->setFrom('margouillat.reunion.it@gmail.com')
            ->setTo($to)
            ->setBody(
                $this->renderView($view, $options), 'text/html');
        $mailer->send($message);
    }

}