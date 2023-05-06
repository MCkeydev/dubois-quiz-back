<?php

namespace App\Repository;

use App\Entity\StudentCopy;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudentCopy>
 *
 * @method StudentCopy|null find($id, $lockMode = null, $lockVersion = null)
 * @method StudentCopy|null findOneBy(array $criteria, array $orderBy = null)
 * @method StudentCopy[]    findAll()
 * @method StudentCopy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentCopyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentCopy::class);
    }

    public function save(StudentCopy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StudentCopy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

        public function findLastGradedCopy(User $user): ?StudentCopy
        {
            return $this->createQueryBuilder('s')
                ->andWhere('s.student = :val')
                ->setParameter('val', $user->getId())
                ->andWhere('s.score IS NOT NULL')
                ->orderBy('s.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }

//    /**
//     * @return StudentCopy[] Returns an array of StudentCopy objects
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

//    public function findOneBySomeField($value): ?StudentCopy
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
