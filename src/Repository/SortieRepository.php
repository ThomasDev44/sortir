<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }


    public function selectSortiesAvecFiltres($leSiteId, $choixSearch, $choixDateStart, $choixDateEnd,
                                             $choixOrganisateur, $choixInscrit, $choixPasInscrit, $choixPassee)
    {

        $qb = $this->createQueryBuilder('s');
        if ($leSiteId != -1) {
            $qb
                ->andWhere('s.site = :leSite')
                ->setParameter('leSite', $leSiteId);
        }
        if ($choixSearch != null) {
            $qb
                ->andWhere('s.nom LIKE :choixSearch')
                ->setParameter('choixSearch', '%' . $choixSearch . '%');
        }
        if ($choixDateStart != null) {
            $qb
                ->andWhere('s.dateHeureDebut >= :choixDateStart')
                ->setParameter('choixDateStart', $choixDateStart);
        }
        if ($choixDateEnd != null) {
            $qb
                ->andWhere('s.dateHeureDebut <= :choixDateEnd')
                ->setParameter('choixDateEnd', $choixDateEnd);
        }
        if ($choixOrganisateur != null) {
            $user = $this->getEntityManager()->getRepository(Participant::class)->find($choixOrganisateur);
            $qb
                ->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }
        if ($choixInscrit != null) {
            $user = $this->getEntityManager()->getRepository(Participant::class)->find($choixInscrit);
            $qb
                ->andWhere(':inscrit MEMBER OF s.participants')
                ->setParameter('inscrit', $user);
        }

        if ($choixPasInscrit != null) {
            $user = $this->getEntityManager()->getRepository(Participant::class)->find($choixPasInscrit);
            $qb
                ->andWhere(':pasInscrit NOT MEMBER OF s.participants')
                ->setParameter('pasInscrit', $user);
        }

        if ($choixPassee != null) {
            $sortiePasse = $this->getEntityManager()->getRepository(Etat::class)->findOneBy(['libelle' => $choixPassee]);
            $qb
                ->andWhere('s.etat = :sortiePasse')
                ->setParameter('sortiePasse', $sortiePasse);
        }
        $requete = $qb->getQuery();
        return $requete->execute();


    }


    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
