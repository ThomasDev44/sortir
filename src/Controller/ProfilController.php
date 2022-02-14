<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ProfilType;
use App\Form\SiteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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


    #[Route('/update/{id}', name: 'update')]
    public function update(
        Request                $request,
                               $id,
        EntityManagerInterface $entityManager)
    {
        $participant = $this->getUser();

        $form = $this->createForm(ProfilType::class, $participant);
        $form->handleRequest($request);


        if ($form->isSubmitted()
            && $form->isValid()
        ) {
            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash('bravo', 'Votre profil a été modifié');
            /*return $this->redirectToRoute('main_accueil', ['id' => $participant->getId()]);*/
        }

        return $this->renderForm('profil/index.html.twig',
            compact("form") // ["monFormulaireIdee" => $monFormulaireIdee]
        );
    }
}
    /*$form = $this->createFormBuilder()
        ->add('username', TextType::class, [
            'label' => 'Pseudo : '
        ])
        ->add('prenom', TextType::class, [
            'label' => 'Prénom : '
        ])
        ->add('nom', TextType::class, [
            'label' => 'Nom : '
        ])
        ->add('telephone', TextType::class, [
            'label' => 'Téléphone : '
        ])
        ->add('mail', TextType::class, [
            'label' => 'Email : '
        ])
        ->add('password', TextType::class, [
            'label' => 'Mot de passe : '
            ])
        ->add('confirmation', TextType::class, [
            'label' => 'Confirmation : '
        ])
        ->add('site', EntityType::class, [
            'label' => 'Site de rattachement : ',
            'class' => Site::class
        ])
        ->add('enregistrer', SubmitType::class, [
            'attr' => ['class' => 'enregistrer'],
        ])
        ->getForm();

    if($request->isMethod('post')){
        return new JsonResponse($request->request->all());
    }


    return $this->render('profil/index.html.twig',
    array('form'=>$form->createView()));
}*/

