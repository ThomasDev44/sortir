<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Sodium\add;


class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil')]
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }

    #[Route('/profil/{id}', name: 'detail')]
    public function detail(
        Participant $participant
    ): Response
    {
        return $this->render('profil/detail.html.twig',
            compact('participant')
        );
    }


    #[Route('/update', name: 'update')]
    public function update(
        Request                     $request,
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        ParticipantRepository       $participantRepository)
    {
        $participant = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $participant]);
        $tousLesParticipants = $participantRepository->findAll();

        $form = $this->createForm(ProfilType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted()
            && $form->isValid()
        ) {
            $erreur = false;


//             est ce que le pseudo est unique
//             utilisation d'une méthode que nous allons ajouter au repository

            foreach ($tousLesParticipants as $value) {

                if ($value->getId() != $participant->getId()) {
                    if (strtolower($value->getUserIdentifier()) == strtolower($participant->getUserIdentifier())) {
                        $this->addFlash('error', 'Pseudo déjà utilisé');
                        $erreur = true;
                    }
                    if (strtolower($value->getMail()) == strtolower($participant->getMail())) {
                        $this->addFlash('error', 'Mail déjà utilisé');
                        $erreur = true;
                    }
                }
            }
            if ($erreur == true) {
                return $this->render('profil/index.html.twig', [
                    'participant' => $participant,
                    'form' => $form->createView(),
                ]);

            } else {

                $entityManager->persist($participant);
                $entityManager->flush();
                $this->addFlash('bravo', 'Votre profil a été modifié');
                /*return $this->redirectToRoute('main_accueil', ['id' => $participant->getId()]);*/
            }


            /*$participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('password')->getData()
                )
            );*/

        }


        return $this->renderForm('profil/index.html.twig',
            compact("form", "participant") // ["monFormulaireIdee" => $monFormulaireIdee]
        );
    }

}


