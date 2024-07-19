<?php

namespace App\Repository;

use App\Entity\Mail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\Traits\PaginationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends ServiceEntityRepository<Mail>
 */
class MailRepository extends ServiceEntityRepository
{
    use PaginationTrait;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        parent::__construct($registry, Mail::class);
        $this->setRequestStack($requestStack);
        $this->queryAlias = 'q';
    }

    public function save(Mail $mail): Mail
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
    //     * @return Mail[] Returns an array of Mail objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Mail
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
