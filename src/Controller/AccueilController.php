<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route ('', name: 'main_')]
class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'accueil')]
    public function index(SiteRepository        $siteRepository,
                          SortieRepository      $sortieRepository,
                          ParticipantRepository $participantRepository,

    ): Response

    {
        $sites = $siteRepository->findAll();
        $sorties = $sortieRepository->findAll();
        return $this->render('accueil/index.html.twig', [
            "sorties" => $sorties,
            "sites" => $sites,
        ]);
    }

}
