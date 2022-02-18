<?php

namespace App\Controller;

use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Service\ChangerEtat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route ('/accueil', name: 'main_')]
class AccueilController extends AbstractController
{
    #[Route('', name: 'accueil')]
    public function index(SiteRepository         $siteRepository,
                          SortieRepository       $sortieRepository,
                          ChangerEtat            $changerEtat,
                          EtatRepository         $etatRepository,
                          EntityManagerInterface $entityManager,

    ): Response

    {
        $changerEtat->verifierEtat();
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
                                       ChangerEtat      $changerEtat,

    ): Response

    {

        $choixSite = filter_input(INPUT_POST, 'sites-select', FILTER_SANITIZE_STRING);

        $choixSearch = filter_input(INPUT_POST, 'search', FILTER_SANITIZE_STRING);

        $choixDateStart = filter_input(INPUT_POST, 'trip-start', FILTER_SANITIZE_STRING);

        $choixDateEnd = filter_input(INPUT_POST, 'trip-end', FILTER_SANITIZE_STRING);

        $choixOrganisateur = filter_input(INPUT_POST, 'organisateur', FILTER_VALIDATE_INT);

        $choixInscrit = filter_input(INPUT_POST, 'inscrit', FILTER_VALIDATE_INT);

        $choixPasInscrit = filter_input(INPUT_POST, 'pasInscrit', FILTER_VALIDATE_INT);

        $choixPassee = filter_input(INPUT_POST, 'passee', FILTER_SANITIZE_STRING);


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

        $changerEtat->verifierEtat();
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
                             ChangerEtat $changerEtat,
    ): Response
    {
        $erreur = false;
        $isInscrit = false;
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $dateNow = new \DateTime('NOW');
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        if ($laSortie->getDateLimiteInscription() < $dateNow) {
            $this->addFlash('error', 'La date de clôture est passée');
            $erreur = true;
        }
        if ($laSortie->getNbInscriptionsMax() == $laSortie->getParticipants()->count()) {
            $this->addFlash('error', 'La sortie est déjà complète');
            $erreur = true;
        }
        if ($laSortie->getOrganisateur() === $user) {
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire sur votre propre sortie');
            $erreur = true;
        }
        if ($laSortie->getEtat() != 'Ouverte') {
            $this->addFlash('error', "L'êtat de la sortie doit être ouverte");
        }
        foreach ($laSortie->getParticipants() as $value) {
            if ($value == $user) {
                $isInscrit = true;
            }
        }
        if ($isInscrit == true) {
            $this->addFlash('error', "Vous êtes déjà inscrit à cette sortie");
            $erreur = true;
        }


        if ($erreur == false) {
            $user = $this->getUser()->getUserIdentifier();
            $user = $participantRepository->findOneBy(['username' => $user]);
            $laSortie->addParticipant($user);
            $entityManager->persist($laSortie);
            $entityManager->flush();
        }

        $changerEtat->verifierEtat();
        $sorties = $sortieRepository->findAll();
        $sites = $siteRepository->findAll();
        return $this->render('accueil/index.html.twig', ['sorties' => $sorties,
            'sites' => $sites,]);
    }

    #[
        Route('/desister/{idSortie}', name: 'desister')]
    public function desister($idSortie,
                             SortieRepository $sortieRepository,
                             ParticipantRepository $participantRepository,
                             SiteRepository $siteRepository,
                             EntityManagerInterface $entityManager,
                             ChangerEtat $changerEtat,
    ): Response
    {

        $erreur = false;
        $isInscrit = false;
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);

        foreach ($laSortie->getParticipants() as $value) {
            if ($value === $user) {
                $isInscrit = true;
            }
        }
        if ($isInscrit == false) {
            $this->addFlash('error', "Vous n'êtes pas inscrit à cette sortie");
            $erreur = true;
        }
        if ($erreur == false) {
            $laSortie->removeParticipant($user);
            $entityManager->persist($laSortie);
            $entityManager->flush();
        }

        $changerEtat->verifierEtat();
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
                            ChangerEtat $changerEtat,
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

        $changerEtat->verifierEtat();
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
                                       SiteRepository $siteRepository,
                                       ParticipantRepository $participantRepository,
                                       ChangerEtat $changerEtat,


    ): Response
    {
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $annuler = true;
        $erreur = false;

        if ($laSortie->getEtat() != 'Ouverte') {
            $this->addFlash('error', "L'êtat de la sortie ne le permet pas, elle doit être ouverte");
            $erreur = true;
        }
        if ($laSortie->getOrganisateur() !== $user) {
            $this->addFlash('error', "Vous n'êtes pas l'organisateur de cette sortie");
            $erreur = true;
        }

        if ($erreur == false) {
            return $this->render('sortie/annuler.html.twig', [

                'sortie' => $laSortie,
                'annuler' => $annuler,
            ]);
        } else {

            $changerEtat->verifierEtat();
            $sorties = $sortieRepository->findAll();
            $sites = $siteRepository->findAll();
            return $this->render('accueil/index.html.twig', [
                'sorties' => $sorties,
                'sites' => $sites,
            ]);
        }

    }

    #[Route('/annuler/{idSortie}', name: 'annuler')]
    public function annuler($idSortie,
                            SortieRepository $sortieRepository,
                            ParticipantRepository $participantRepository,
                            SiteRepository $siteRepository,
                            EntityManagerInterface $entityManager,
                            EtatRepository $etatRepository,
                            ChangerEtat $changerEtat,
    ): Response
    {
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        $etat = $etatRepository->findOneBy(['libelle' => 'Annulée']);
        $motif = filter_input(INPUT_POST, 'motif', FILTER_SANITIZE_STRING);
        $admin = false;
        $erreur = false;
        foreach ($user->getRoles() as $value) {
            if ($value == 'ROLE_ADMIN') {
                $admin = true;
            }
        }
        if ($laSortie->getOrganisateur() !== $user) {
            $this->addFlash('error', "Vous n'êtes pas l'organisateur de cette sortie");
            $erreur = true;
        }
        if ($laSortie->getEtat()->getLibelle() != 'Ouverte') {
            $this->addFlash('error', "L'êtat de la sortie ne permet pas cette action");
            $erreur = true;
        }


        if (($erreur == false) or ($admin == true)) {
            $laSortie->setMotif($motif);
            $laSortie->setEtat($etat);
            $entityManager->persist($laSortie);
            $entityManager->flush();
        }

        $changerEtat->verifierEtat();
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
                             ParticipantRepository $participantRepository,
                             SiteRepository $siteRepository,
                             ChangerEtat $changerEtat,


    ): Response
    {
        $afficher = true;
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        $erreur = false;

        if ($laSortie->getEtat() == 'Créée') {
            $this->addFlash('error', "La sortie est toujours en cours de création");
            $erreur = true;
        }
        $changerEtat->verifierEtat();
        if ($erreur == false) {
            return $this->render('sortie/show.html.twig', [
                "sortie" => $laSortie,
                "afficher" => $afficher,
            ]);

        } else {
            $sorties = $sortieRepository->findAll();
            $sites = $siteRepository->findAll();
            return $this->render('accueil/index.html.twig', [
                'sorties' => $sorties,
                'sites' => $sites,
            ]);
        }
    }

    #[Route('/modifier/{idSortie}', name: 'modifier')]
    public function modifier($idSortie,
                             SortieRepository $sortieRepository,
                             Request $request,
                             EntityManagerInterface $entityManager,
                             SiteRepository $siteRepository,
                             ParticipantRepository $participantRepository,
                             ChangerEtat $changerEtat,

    ): Response
    {
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);

        $form = $this->createForm(SortieType::class, $laSortie);
        $form->handleRequest($request);
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
        $erreur = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if (($laSortie->getDateLimiteInscription() <= new \DateTime()) or ($laSortie->getDateHeureDebut() <= $laSortie->getDateLimiteInscription())) {
                $this->addFlash('error', "Vous ne pouvez pas mettre une date inférieure à la date du jour. La date limite d'inscription doit être inférieure à la date de la sortie");
                $erreur = true;
            }
            if ($laSortie->getNbInscriptionsMax() <= 0) {
                $this->addFlash('error', 'Le nombre de places doit être supérieur à 0');
                $erreur = true;
            }
            if ($laSortie->getDuree() <= 0) {
                $this->addFlash('error', 'La durée doit être supérieure à 0');
                $erreur = true;
            }
            if ($laSortie->getEtat() != "Créée") {
                $this->addFlash('error', "Vous ne pouvez pas modifier une sortie qui n'est pas en cours de création");
                $erreur = true;
            }
            if ($laSortie->getOrganisateur() !== $user) {
                $this->addFlash('error', "Vous ne pouvez pas modifier une sortie dont vous n'êtes pas l'oganisateur");
                $erreur = true;
            }

            if ($erreur == false) {
                $changerEtat->verifierEtat();
                $sites = $siteRepository->findAll();
                $sorties = $sortieRepository->findAll();
                $entityManager->persist($laSortie);
                $entityManager->flush();
                return $this->render('accueil/index.html.twig', [
                    "sorties" => $sorties,
                    "sites" => $sites,
                ]);
            }
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
                              ParticipantRepository $participantRepository,
                              ChangerEtat $changerEtat,
    ): Response
    {
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $erreur = false;
        $user = $participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);

        if ($laSortie->getEtat() != "Créée") {
            $this->addFlash('error', "Vous ne pouvez pas modifier une sortie qui n'est pas en cours de création");
            $erreur = true;
        }
        if ($laSortie->getOrganisateur() !== $user) {
            $this->addFlash('error', "Vous ne pouvez pas modifier une sortie dont vous n'êtes pas l'oganisateur");
            $erreur = true;
        }

        if ($erreur == false) {
            $entityManager->remove($laSortie);
            $entityManager->flush();
        }
        $changerEtat->verifierEtat();
        $sites = $siteRepository->findAll();
        $sorties = $sortieRepository->findAll();

        return $this->render('accueil/index.html.twig', [
            "sorties" => $sorties,
            "sites" => $sites,
        ]);
    }


}