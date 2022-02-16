<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    #[Route('/mail', name: 'mail')]
    public function index(): Response
    {
        return $this->render('mail/index.html.twig', [
            'controller_name' => 'MailController',
        ]);
    }

    #[Route('/mail/{nom}', name: 'mail')]
    public function envoyerUnMail(
        $nom,
        $prenom,
        MailerInterface $mailer
    ): Response
    {
        $email = (new Email())
            ->from('admin@eni.fr')
            ->to($prenom.$nom. '@eni.com')
            ->subject('Sortie organisée le 14/03/2022')
            ->text('Salut ! Je t\'écris à propos de la sortie prévue le 14. Le rendez-vous est bien à 19h ? 
                            Bonne journée! Bisous !');
        $mailer->send($email);
        $this->addFlash("mail", "Mail envoyé");
        return $this->render('reset_password/redirection.html.twig');
    }
}


