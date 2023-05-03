<?php

namespace App\Repository;

use App\Entity\Evaluation;
use App\Entity\Formation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
     * @return Evaluation[] Returns an array of Evaluation objects
     */
    public function findIncomingEvaluations(Formation $formation, User $user): array
    {
        // We fetch all the evaluations for which the user created a StudenCopy
        $subQb = $this->createQueryBuilder('e1')
            ->select('e1')
            ->join('e1.studentCopies', 's')
            ->andWhere('s.student = :user');

        $qb = $this->createQueryBuilder('e');

        /**
         * We then fetch all the evaluations for the current formation,
         * where evaluation.id is not in the previous query
         * (meaning we fetch all evaluations with no studentCopy)
         */
        return $qb->leftjoin('e.studentCopies', 'sc')
            ->andWhere('e.formation = :formation')
            ->setParameter('formation', $formation)
            ->andWhere($qb->expr()->notIn('e.id', $subQb->getDQL()))
            ->setParameter('user', $user)
            ->andWhere('e.endsAt > :date')
            ->setParameter('date', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

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
