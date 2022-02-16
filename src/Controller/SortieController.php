<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route('/admin', name: 'sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository, EtatRepository $etatRepository, SiteRepository $siteRepository, SortieRepository $sortieRepository): Response
    {

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        $erreur = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if (($sortie->getDateLimiteInscription() <= new \DateTime()) or ($sortie->getDateHeureDebut() <= $sortie->getDateLimiteInscription())) {
                $this->addFlash('error', "Vous ne pouvez pas mettre une date inférieure à la date du jour. La date limite d'inscription doit être inférieure à la date de la sortie");
                $erreur = true;
            }
            if ($sortie->getNbInscriptionsMax() <= 0) {
                $this->addFlash('error', 'Le nombre de places doit être supérieur à 0');
                $erreur = true;
            }
            if ($sortie->getDuree() <= 0) {
                $this->addFlash('error', 'La durée doit être supérieure à 0');
                $erreur = true;
            }

            if ($erreur == false) {
                $sortie->setOrganisateur($participantRepository->findOneBy(['username' => $this->getUser()->getUserIdentifier()]));
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
                $entityManager->persist($sortie);
                $entityManager->flush();
                $sorties = $sortieRepository->findAll();
                $sites = $siteRepository->findAll();
                return $this->redirectToRoute('main_accueil', [
                    "sorties" => $sorties,
                    "sites" => $sites,

                ], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/annulerRedirection/{idSortie}', name: 'annuler_sortie_admin')]
    public function annulerRedirection($idSortie,
                                       SortieRepository $sortieRepository,

    ): Response
    {
        $laSortie = $sortieRepository->findOneBy(['id' => $idSortie], []);
        $annuler = true;
        return $this->render('sortie/annuler.html.twig', [

            'sortie' => $laSortie,
            'annuler' => $annuler,
        ]);

    }

}
