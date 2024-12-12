<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function getAllNotificationsByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $queryBuilder = $this->createQueryBuilder('n');

        if (isset($data['type'])) {
            $queryBuilder->andWhere('n.type = :type')
                ->setParameter('type', $data['type']);
        }

        if (isset($data['userId'])) {
            $queryBuilder->andWhere('n.user = :userId')
                ->setParameter('userId', $data['userId']);
        }

        if (isset($data['isRead'])) {
            $queryBuilder->andWhere('n.isRead = :isRead')
                ->setParameter('isRead', $data['isRead']);
        }

        $queryBuilder->setFirstResult($itemsPerPage * ($page - 1))
            ->setMaxResults($itemsPerPage);

        $paginator = new Paginator($queryBuilder);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $itemsPerPage);

        return [
            'notifications' => $paginator->getQuery()->getResult(),
            'totalPageCount' => $pagesCount,
            'totalItems' => $totalItems,
        ];
    }
}
