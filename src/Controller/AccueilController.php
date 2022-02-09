<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use DateInterval;
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
        $choixSite = $request->request->get('sites-select');
        $choixSearch = $request->request->get('search');
        $choixDateStart = $request->request->get('trip-start');
        $choixDateEnd = $request->request->get('trip-end');
        $choixOrganisateur = $request->request->get('organisateur');
        $choixInscrit = $request->request->get('inscrit');
        $choixPasInscrit = $request->request->get('pasInscrit');
        $choixPassee = $request->request->get('passee');




        if ($choixSite != 'Tous'){
            $leSiteId = $siteRepository->findOneBy(['nom' => $choixSite]);
            $leSiteId = $leSiteId->getId();
        }
        else {
            $leSiteId = -1;
        }
        if ((($choixDateStart != null) and ($choixDateEnd == null)) or (($choixDateEnd != null) and $choixDateStart == null)) {
            $this->addFlash('error', 'Veuillez sélectionner les deux dates');
            $sorties = $sortieRepository->findAll();

        } else {
            $sorties = $sortieRepository->selectSortiesAvecFiltres($leSiteId, $choixSearch, $choixDateStart, $choixDateEnd,
                $choixOrganisateur, $choixInscrit, $choixPasInscrit, $choixPassee);
        }
        $response = new Response();

        $sites = $siteRepository->findAll();
        return $this->render('accueil/index.html.twig', [
            "sorties" => $sorties,
            "sites" => $sites,
            "leSite" => $choixSite,
            "choixSearch" => $choixSearch,
            "choixDateStart" => $choixDateStart,
            "choixDateEnd" => $choixDateEnd,
            "choixOrganisateur" => $choixOrganisateur,
            'choixInscrit' => $choixInscrit,
            'choixPasInscrit' => $choixPasInscrit,
            'choixPassee' => $choixPassee,
        ]);
    }

}