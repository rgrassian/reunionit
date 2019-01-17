<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    /**
     * Page d'accueil de l'application.
     * @Route("/", name="index")
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(\Swift_Mailer $mailer)
    {
//        $message = (new \Swift_Message('Hello Email'))
//            ->setFrom('margouillat.reunion.it@gmail.com')
//            ->setTo('remi.grassian@gmail.com')
//            ->setBody(
//                $this->renderView(
//                    'email/registration.html.twig',
//                    ['name' => 'Michel']
//                ),
//                'text/html'
//            )
//        ;
//        $mailer->send($message);

        return $this->render('front/index.html.twig');
    }
}
