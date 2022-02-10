<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SiteType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route ('/accueil', name: 'main_')]
class AccueilController extends AbstractController
{
    #[Route('', name: 'accueil')]
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

    #[Route('/recherche', name: 'recherche')]
    public function rechercheParFiltre(SiteRepository   $siteRepository,
                                       SortieRepository $sortieRepository,
                                       Request          $request,
    ): Response

    {

        $choixSite = filter_input(INPUT_POST, 'sites-select', FILTER_SANITIZE_STRING);

        $choixSearch = filter_input(INPUT_POST, 'search', FILTER_SANITIZE_STRING);

        $choixDateStart = filter_input(INPUT_POST, 'trip-start', FILTER_SANITIZE_STRING);

        $choixDateEnd = filter_input(INPUT_POST, 'trip-end', FILTER_SANITIZE_STRING);

        $choixOrganisateur = filter_input(INPUT_POST, 'organisateur', FILTER_VALIDATE_INT);

        $choixInscrit = filter_input(INPUT_POST, 'inscrit', FILTER_VALIDATE_INT);

        $choixPasInscrit = filter_input(INPUT_POST, 'pasInscrit', FILTER_VALIDATE_INT);

        $choixPassee = filter_input(INPUT_POST, 'pasInscrit', FILTER_SANITIZE_STRING);


        if ($choixSite != 'Tous') {
            $leSiteId = $siteRepository->findOneBy(['nom' => $choixSite]);
            $leSiteId = $leSiteId->getId();
        } else {
            $leSiteId = -1;
        }
        if ((($choixDateStart != null) and ($choixDateEnd == null)) or (($choixDateEnd != null) and $choixDateStart == null)) {
            $this->addFlash('error', 'Veuillez sélectionner les deux dates');
            $sorties = $sortieRepository->findAll();

        } else {
            $sorties = $sortieRepository->selectSortiesAvecFiltres($leSiteId, $choixSearch, $choixDateStart, $choixDateEnd,
                $choixOrganisateur, $choixInscrit, $choixPasInscrit, $choixPassee);
        }

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

    #[Route('/inscrire/{idSortie}', name: 'inscrire')]
    public function inscrire($idSortie,
                             SortieRepository $sortieRepository,
                             ParticipantRepository $participantRepository,
                             SiteRepository $siteRepository,
                             EntityManagerInterface $entityManager,
    ): Response
    {
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $dateNow = new \DateTime('NOW');
        if ($laSortie->getDateLimiteInscription() >= $dateNow) {
            $user = $this->getUser()->getUserIdentifier();
            $user = $participantRepository->findOneBy(['username' => $user]);
            $laSortie->addParticipant($user);
            $entityManager->persist($laSortie);
            $entityManager->flush();
        } else {
            $this->addFlash('error', 'La date de clôture est passée');
        }

        $sorties = $sortieRepository->findAll();
        $sites = $siteRepository->findAll();
        return $this->render('accueil/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

    #[Route('/desister/{idSortie}', name: 'desister')]
    public function desister($idSortie,
                             SortieRepository $sortieRepository,
                             ParticipantRepository $participantRepository,
                             SiteRepository $siteRepository,
                             EntityManagerInterface $entityManager,
    ): Response
    {
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        $laSortie->removeParticipant($user);
        $entityManager->persist($laSortie);
        $entityManager->flush();

        $sorties = $sortieRepository->findAll();
        $sites = $siteRepository->findAll();
        return $this->render('accueil/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

    #[Route('/publier/{idSortie}', name: 'publier')]
    public function publier($idSortie,
                            SortieRepository $sortieRepository,
                            ParticipantRepository $participantRepository,
                            SiteRepository $siteRepository,
                            EntityManagerInterface $entityManager,
                            EtatRepository $etatRepository,
    ): Response
    {
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        if ($laSortie->getOrganisateur() === $user) {
            $laSortie->setEtat($etat);
            $entityManager->persist($laSortie);
            $entityManager->flush();
        } else {
            $this->addFlash('error', "Vous n'êtes pas l'organisateur de cette sortie");
        }


        $sorties = $sortieRepository->findAll();
        $sites = $siteRepository->findAll();
        return $this->render('accueil/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

    #[Route('/annulerRedirection/{idSortie}', name: 'annulerRedirection')]
    public function annulerRedirection($idSortie,
                                       SortieRepository $sortieRepository,

    ): Response
    {
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $annuler = true;
        return $this->render('sortie/show.html.twig', [

            'sortie' => $laSortie,
            'annuler' => $annuler,
        ]);

    }

    #[Route('/annuler/{idSortie}', name: 'annuler')]
    public function annuler($idSortie,
                            SortieRepository $sortieRepository,
                            ParticipantRepository $participantRepository,
                            SiteRepository $siteRepository,
                            EntityManagerInterface $entityManager,
                            EtatRepository $etatRepository,
    ): Response
    {
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        $etat = $etatRepository->findOneBy(['libelle' => 'Annulée']);
        if ($laSortie->getOrganisateur() === $user and $laSortie->getEtat()->getLibelle() == 'Ouverte') {
            $laSortie->setEtat($etat);
            $entityManager->persist($laSortie);
            $entityManager->flush();
        } else {
            $this->addFlash('error', "Vous n'êtes pas l'organisateur de cette sortie ou l'etat de la sortie ne le permet pas !");
        }


        $sorties = $sortieRepository->findAll();
        $sites = $siteRepository->findAll();
        return $this->render('accueil/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

    #[Route('/afficher/{idSortie}', name: 'afficher')]
    public function afficher($idSortie,
                             SortieRepository $sortieRepository,
    ): Response
    {
        $afficher = true;
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);

        return $this->render('sortie/show.html.twig', [
            "sortie" => $laSortie,
            "afficher" => $afficher,
        ]);
    }

    #[Route('/modifier/{idSortie}', name: 'modifier')]
    public function modifier($idSortie,
                             SortieRepository $sortieRepository,
                             Request $request,
                             EntityManagerInterface $entityManager,
                             SiteRepository $siteRepository,

    ): Response
    {
        $sites = $siteRepository->findAll();
        $sorties = $sortieRepository->findAll();
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);

        $form = $this->createForm(SortieType::class, $laSortie);
        $form->handleRequest($request);


        if ($form->isSubmitted() and $form->isValid()) {
            $entityManager->persist($laSortie);
            $entityManager->flush();
            return $this->render('accueil/index.html.twig', [
                "sorties" => $sorties,
                "sites" => $sites,
            ]);
        }
        return $this->render('sortie/edit.html.twig', [
            'formSortie' => $form->createView(),
            'sortie' => $laSortie,
        ]);
    }

    #[Route('/supprimer/{idSortie}', name: 'supprimer')]
    public function supprimer($idSortie,
                              SortieRepository $sortieRepository,
                              EntityManagerInterface $entityManager,
                              SiteRepository $siteRepository,
    ): Response
    {
        $sites = $siteRepository->findAll();
        $sorties = $sortieRepository->findAll();
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $entityManager->remove($laSortie);
        $entityManager->flush();

        return $this->render('accueil/index.html.twig', [
            "sorties" => $sorties,
            "sites" => $sites,
        ]);
    }


}