<?php

namespace App\Controller;


use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/redirection', name: 'redirection')]
class RedirectionController extends AbstractController
{
    #[Route('', name: '')]
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

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    #[Route('/verification', name: 'verification')]
    public function verification(ParticipantRepository       $participantRepository,
                                 UserPasswordHasherInterface $userPasswordHasher,
                                 EntityManagerInterface      $entityManager,


    ): Response
    {
        $pseudo = filter_input(INPUT_POST, 'pseudo', FILTER_SANITIZE_STRING);
        $mail = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $confirmation = filter_input(INPUT_POST, 'confirmation', FILTER_SANITIZE_STRING);
        $erreur = true;

        $tousLesParticipants = $participantRepository->findAll();

        foreach ($tousLesParticipants as $value) {
            if (($value->getUserIdentifier() == $pseudo) and ($value->getMail() == $mail)) {
                $erreur = false;
            }
        }

        if ($erreur == true) {
            $this->addFlash('error', "L'utilisateur n'existe pas");
            return $this->render('reset_password/redirection.html.twig');
        } else {
            if ($password == $confirmation) {

                $user = $participantRepository->findOneBy(['username' => $pseudo]);
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $password
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->render('security/login.html.twig');
            } else {
                $this->addFlash('error', "Le mot de passe et la confirmation ne sont pas identiques");
                return $this->render('reset_password/redirection.html.twig');

            }
        }


    }

}


