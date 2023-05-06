<?php

namespace App\Repository;

use App\Entity\Evaluation;
use App\Entity\Formation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evaluation>
 *
 * @method Evaluation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evaluation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evaluation[]    findAll()
 * @method Evaluation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }

    public function save(Evaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Evaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Cherche dans la table Evaluation toutes les évaluations en cours, pour une formation donnée,
     * et un utilisateur donné, où l'utilisateur n'a pas encore de copie.
     *
     * @return Evaluation[]
     */
    public function findOngoingEvaluations(Formation $formation, User $user): array
    {
        // Requête de toutes les évaluations ou l'utilisateur a une copie
        $subQb = $this->createQueryBuilder('e1')
            ->select('e1')
            ->join('e1.studentCopies', 's')
            ->andWhere('s.student = :user');

        $qb = $this->createQueryBuilder('e');

        /*
         * Nous récupérons ensuite toutes les évaluations pour la formation actuelle,
         * où l'identifiant de l'évaluation n'est pas dans la requête précédente
         * (cela signifie que nous récupérons toutes les évaluations sans copie d'étudiant).
         */
        return $qb->leftjoin('e.studentCopies', 'sc')
            ->andWhere('e.formation = :formation')
            ->setParameter('formation', $formation)
            ->andWhere($qb->expr()->notIn('e.id', $subQb->getDQL()))
            ->setParameter('user', $user)
            ->andWhere('e.endsAt > :date')
            ->andWhere('e.startsAt < :date')
            ->setParameter('date', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }


    public function findUpcomingEvaluations()
//    /**
//     * @return Evaluation[] Returns an array of Evaluation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Evaluation
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
