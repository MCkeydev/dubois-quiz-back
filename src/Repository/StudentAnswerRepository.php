<?php

namespace App\Repository;

use App\Entity\StudentAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentAnswer>
 *
 * @method StudentAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentAnswer[]    findAll()
 * @method StudentAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentAnswer::class);
    }

    /**
     * Enregistre l'entité StudentAnswer.
     *
     * @param StudentAnswer $entity L'entité StudentAnswer à enregistrer.
     * @param bool $flush Indique s'il faut effectuer une opération de flush après l'enregistrement.
     *
     * @return void
     */
     public function save(StudentAnswer $entity, bool $flush = false): void
     {
         $this->getEntityManager()->persist($entity);

         if ($flush) {
             $this->getEntityManager()->flush();
         }
     }

    /**
     * Supprime l'entité StudentAnswer.
     *
     * @param StudentAnswer $entity L'entité StudentAnswer à supprimer.
     * @param bool $flush Indique s'il faut effectuer une opération de flush après la suppression.
     *
     * @return void
     */
    public function remove(StudentAnswer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return StudentAnswer[] Returns an array of StudentAnswer objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StudentAnswer
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
