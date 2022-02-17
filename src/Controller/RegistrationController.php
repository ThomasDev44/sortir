<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use App\Security\ConnexionAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[isGranted('ROLE_ADMIN')]
    #[Route('/register', name: 'app_register')]
    public function register(Request                     $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface  $userAuthenticator,
                             ConnexionAuthenticator      $authenticator,
                             EntityManagerInterface      $entityManager,
                             ParticipantRepository       $participantRepository,

    ): Response
    {
        $user = new Participant();
        $tousLesParticipants = $participantRepository->findAll();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $erreur = false;

        if ($form->isSubmitted() && $form->isValid()) {

            // encode the plain password
            $user->setActif('false');
            $isAdmin = $user->getAdministrateur();
            $pseudo = $user->getUserIdentifier();
            $mail = $user->getMail();

            foreach ($tousLesParticipants as $value) {
                if ($value->getUserIdentifier() == $pseudo) {
                    $erreur = true;
                    $this->addFlash('error', "Ce pseudo existe déjà");
                }
                if ($value->getMail() == $mail) {
                    $erreur = true;
                    $this->addFlash('error', "Ce mail existe déjà");
                }
            }
            if ($isAdmin == false) {
                $user->setRoles(['ROLE_USER']);
            } else {
                $user->setRoles(['ROLE_ADMIN']);
            }


            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            if ($erreur == false) {
                $entityManager->persist($user);
                $entityManager->flush();
                // do anything else you need here, like send an email
                return $this->redirectToRoute('app_register');
                /*return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request);*/
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
