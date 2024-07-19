<?php

namespace App\Repository\Traits;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;

trait PaginationTrait
{
    private $requestStack;
    private $queryAlias;

    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    public function findAllPaginated($queryBuilder): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $sort = $request->query->getString('sort', 'id');
        $order = $request->query->getString('order', 'ASC');

        if (is_array($sort)) {

            $queryBuilder->orderBy("{$this->queryAlias}.{$sort[0]}", $order);

            for ($i = 1; $i < count($sort); $i++) {
                $queryBuilder->addOrderBy("{$this->queryAlias}.{$sort[$i]}", $order);
            }

        } else {
            $queryBuilder->orderBy("{$this->queryAlias}.{$sort}", $order);
        }

        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator =  new Paginator($queryBuilder);

        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        return [
            'items' => $queryBuilder->getQuery()->getResult(),
            'current_page' => $page,
            'total_pages' => (int) $totalPages,
            'limit' => $limit,
            'sort' => $sort,
            'order' => $order
        ];
    }
}
