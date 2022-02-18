<?php

namespace App\Service;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChangerEtat
{
    protected $depotSortie;
    protected $depotEtat;
    protected $entity;

    /**
     * @param $depotSortie
     * @param $depotEtat
     * @param $entity
     */

    public function __construct(SortieRepository $depotInject, EtatRepository $depotEtat, EntityManagerInterface $entityManager)
    {
        $this->depotSortie = $depotInject;
        $this->depotEtat = $depotEtat;
        $this->entity = $entityManager;
    }

    public function verifierEtat(): void
    {
        $sorties = $this->depotSortie->findAll();
        $now = new \DateTime();
        $now->modify('+ 1 hour');

        foreach ($sorties as $value) {
            if ($value->getEtat() != 'Annulée' and $value->getEtat() != 'Créée') {

                $dateHeureDebut = $value->getDateHeureDebut();
                if ($value->getNbInscriptionsMax() == $value->getParticipants()->count() or $value->getDateLimiteInscription() < $now) {
                    $value->setEtat(($this->depotEtat->findOneBy(['libelle' => 'Clôturée'])));
                } else {
                    $value->setEtat(($this->depotEtat->findOneBy(['libelle' => 'Ouverte'])));
                }

                if ($dateHeureDebut <= $now) {
                    $value->setEtat($this->depotEtat->findOneBy(['libelle' => 'Activité en cours']));
                    $this->entity->persist($value);
                    $this->entity->flush();
                }

                if ($dateHeureDebut->modify('+ ' . $value->getDuree() . 'minutes') <= $now) {
                    $value->setEtat($this->depotEtat->findOneBy(['libelle' => 'Passée']));

                }
                $dateHeureDebut->modify('- ' . $value->getDuree() . 'minutes');

                $this->entity->persist($value);
                $this->entity->flush();
            }
        }

    }
}