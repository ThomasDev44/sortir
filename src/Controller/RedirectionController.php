<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectionController extends AbstractController
{
    #[Route('/redirection', name: 'redirection')]
    public function index(): Response
    {
        return $this->render('reset_password/redirection.html.twig', [
            'controller_name' => 'RedirectionController',
        ]);
    }
}
