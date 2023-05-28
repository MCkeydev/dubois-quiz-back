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

    /**
     * Enregistre une entité Evaluation dans la base de données.
     *
     * @param Evaluation $entity L'entité Evaluation à enregistrer.
     * @param bool $flush (optionnel) Indique s'il faut effectuer un flush après l'enregistrement. Par défaut, false.
     */
    public function save(Evaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une entité Evaluation de la base de données.
     *
     * @param Evaluation $entity L'entité Evaluation à supprimer.
     * @param bool $flush (optionnel) Indique s'il faut effectuer un flush après la suppression. Par défaut, false.
     */
    public function remove(Evaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Cherche dans la table Evaluation toutes les évaluations en cours de l'utilisateur
     * où l'utilisateur n'a pas encore soumis de copie.
     *
     * @return Evaluation[]
     */
    public function findOngoingEvaluations(User $user): array
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
        return $qb->innerJoin('e.formation', 'f')
            ->innerJoin('f.users', 'u')
            ->andWhere('u.id = :id')
            ->setParameter('id', $user->getId())
            ->andWhere($qb->expr()->notIn('e.id', $subQb->getDQL()))
            ->setParameter('user', $user)
            ->andWhere('e.endsAt > :date')
            ->andWhere('e.startsAt < :date')
            ->setParameter('date', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère toutes les évaluations à venir de l'utilisateur.
     *
     * @param User $user L'utilisateur pour lequel rechercher les évaluations à venir.
     * @return Evaluation[] Un tableau d'objets Evaluation.
     */
    public function findIncomingEvaluations(User $user)
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.formation', 'f')
            ->innerJoin('f.users', 'u')
            ->andWhere('u.id = :id')
            ->andWhere('e.startsAt > :date')
            ->setParameter('date', new \DateTimeImmutable())
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les évaluations du formateur dont la date de fin est passée et pour lesquelles au moins une copie non corrigée existe.
     *
     * @param User $user L'utilisateur formateur pour lequel rechercher les évaluations à corriger.
     * @return array|null Un tableau d'objets Evaluation ou null.
     */
    public function findEvaluationsToGrade(User $user): array|null
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->innerJoin('e.studentCopies', 'sc')
            ->andWhere('e.author = :user')
            ->setParameter('user', $user)
            ->andWhere('sc.commentary IS NULL')
            ->andWhere('e.endsAt <= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère toutes les évaluations créées par un utilisateur où aucune copie n'existe.
     *
     * @param User $user L'utilisateur pour lequel rechercher les évaluations sans copies.
     * @return Evaluation[] Un tableau d'objets Evaluation.
     */
    public function findUserEvaluationsWithoutCopies(User $user): array
    {
        // Requête de toutes les évaluations qui ont des copies
        $subQb = $this->createQueryBuilder('e1')
            ->select('e1.id')
            ->join('e1.studentCopies', 's');

        $qb = $this->createQueryBuilder('e');

        // Nous récupérons toutes les évaluations créées par l'utilisateur
        // où l'ID de l'évaluation n'est pas dans la requête précédente
        return $qb->where('e.author = :user')
            ->setParameter('user', $user)
            ->andWhere($qb->expr()->notIn('e.id', $subQb->getDQL()))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Evaluation[] Returns an array of Evaluation objects
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

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
