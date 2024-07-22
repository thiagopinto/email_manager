<?php

namespace App\Repository;

use App\Entity\BouncedEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BouncedEmail>
 */
class BouncedEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BouncedEmail::class);
    }

    public function save(BouncedEmail $mail): BouncedEmail
    {

        $entityManager = $this->getEntityManager();
        $entityManager->persist($mail);

        return $mail;
    }

    public function findAllPagination(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $search = $request->query->get('search', null);

        $queryBuilder = $this->createQueryBuilder($this->queryAlias);

        if ($search) {
            /*
            $queryBuilder->andWhere("{$this->queryAlias}.email ILIKE :search")
                        ->setParameter('search', '%' . $search . '%');
            */
            $queryBuilder->andWhere($queryBuilder->expr()->like('LOWER(' . $this->queryAlias . '.email)', ':search'))
                         ->setParameter('search', '%' . strtolower($search) . '%');
        }

        return $this->findAllPaginated($queryBuilder);

    }

    //    /**
    //     * @return BouncedEmail[] Returns an array of BouncedEmail objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?BouncedEmail
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
