<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RedirectionController extends AbstractController
{
    #[Route('/redirection', name: 'redirection')]
    public function index(): Response
    {

       /* if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }*/

    #

        return $this->render('reset_password/redirection.html.twig', [
            'controller_name' => 'RedirectionController',
        ]);
    }
}
