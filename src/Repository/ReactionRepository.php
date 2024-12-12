<?php

namespace App\Repository;

use App\Entity\Reaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reaction>
 */
class ReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reaction::class);
    }

    /**
     * @param array $data
     * @param int $itemsPerPage
     * @param int $page
     * @return array
     */
    public function getAllReactionsByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $queryBuilder = $this->createQueryBuilder('reaction');

        if (isset($data['type'])) {
            $queryBuilder->andWhere('reaction.type = :type')
                ->setParameter('type', $data['type']);
        }

        if (isset($data['userId'])) {
            $queryBuilder->andWhere('reaction.user = :userId')
                ->setParameter('userId', $data['userId']);
        }

        $queryBuilder->setFirstResult($itemsPerPage * ($page - 1))
            ->setMaxResults($itemsPerPage);

        $paginator = new Paginator($queryBuilder);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $itemsPerPage);

        return [
            'reactions' => $paginator->getQuery()->getResult(),
            'totalPageCount' => $pagesCount,
            'totalItems' => $totalItems,
        ];
    }
}
