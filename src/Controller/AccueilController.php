<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route ('', name: 'main_')]
class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'accueil')]
    public function index(SiteRepository   $siteRepository,
                          SortieRepository $sortieRepository,

    ): Response

    {
        $sites = $siteRepository->findAll();
        $sorties = $sortieRepository->findAll();
        return $this->render('accueil/index.html.twig', [
            "sorties" => $sorties,
            "sites" => $sites,
        ]);
    }

    #[Route('/accueil/recherche', name: 'recherche')]
    public function rechercheParFiltre(SiteRepository   $siteRepository,
                                       SortieRepository $sortieRepository,
                                       Request          $request,
    ): Response

    {
        $choixSite = $request->request->get('sites');
        $choixSearch = $request->request->get('search');
        $choixDateStart = $request->request->get('trip-start');
        $choixDateEnd = $request->request->get('trip-end');
        $choixOrganisateur = $request->request->get('organisateur');
        $choixInscrit = $request->request->get('inscrit');
        $choixPasInscrit = $request->request->get('pasInscrit');
        $choixPassee = $request->request->get('passee');
        $sorties = $sortieRepository->findAll();
        $sites = $siteRepository->findAll();
        return $this->render('accueil/index.html.twig', [
            "sorties" => $sorties,
            "sites" => $sites,
        ]);
    }

}