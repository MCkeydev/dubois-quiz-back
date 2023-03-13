<?php

namespace App\Repository;

use App\Entity\SutdentCopy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SutdentCopy>
 *
 * @method SutdentCopy|null find($id, $lockMode = null, $lockVersion = null)
 * @method SutdentCopy|null findOneBy(array $criteria, array $orderBy = null)
 * @method SutdentCopy[]    findAll()
 * @method SutdentCopy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SutdentCopyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SutdentCopy::class);
    }

    public function save(SutdentCopy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SutdentCopy $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SutdentCopy[] Returns an array of SutdentCopy objects
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

//    public function findOneBySomeField($value): ?SutdentCopy
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
