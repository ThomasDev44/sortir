<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                "label" => "Nom de la sortie : "
            ])
            ->add('dateHeureDebut', null, [
                "label" => "Date et heure de la sortie : "
            ])
            ->add('dateLimiteInscription', null, [
                "label" => "Date limite d'inscription : "
            ])
            ->add('nbInscriptionsMax', null, [
                "label" => "Nombre de place : "
            ])
            ->add('duree', null, [
                "label" => "Durée : "
            ])
            ->add('InfosSortie', null, [
                "label" => "Description et infos : "
            ])
            ->add('participants')
            ->add('organisateur')
            ->add('site')
            ->add('etat')
            ->add('lieu')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
