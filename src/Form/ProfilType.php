<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Pseudo :'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom :'
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom :'
            ])
            ->add('telephone', null, [
                'label' => 'Téléphone :'
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Email :'
            ])
            /*->add('password', RepeatedType::class, array(
                'type'              => PasswordType::class,
                'label'             => 'Password : ',
                'mapped'            => false,
                'first_options'     => array('label' => 'Mot de passe : '),
                'second_options'    => array('label' => 'Confirmation : '),
                'invalid_message' => 'Le mot de passe doit être identique',
            ))*/
            ->add('site', EntityType::class, [
                'label' => 'Site de rattachement :',
                'class' => Site::class,
            ])
            ->add('fichierImage', VichFileType::class, [
                "label" => "Ajouter une image",
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
            ])
            ->add('enregistrer', SubmitType::class, [
                'attr' => ['class' => 'enregistrer'],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
