<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlablaController extends AbstractController
{
    #[Route('/blabla', name: 'blabla')]
    public function index(): Response
    {
        return $this->render('blabla/index.html.twig', [
            'controller_name' => 'BlablaController',
        ]);
    }
}
