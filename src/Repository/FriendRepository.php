<?php

namespace App\Repository;

use App\Entity\Friend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
//use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friend>
 */
class FriendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

//    /**
//     * @param array $data
//     * @param int $itemsPerPage
//     * @param int $page
//     * @return array
//     */
//    public function getAllFriendsByFilter(array $data, int $itemsPerPage, int $page): array
//    {
//        $queryBuilder = $this->createQueryBuilder('friend');
//
//        if (isset($data['status'])) {
//            $queryBuilder->andWhere('friend.status = :status')
//                ->setParameter('status', $data['status']);
//        }
//
//        if (isset($data['userId'])) {
//            $queryBuilder->andWhere('friend.user = :userId')
//                ->setParameter('userId', $data['userId']);
//        }
//
//        $queryBuilder->setFirstResult($itemsPerPage * ($page - 1))
//            ->setMaxResults($itemsPerPage);
//
//        $paginator = new Paginator($queryBuilder);
//        $totalItems = count($paginator);
//        $pagesCount = ceil($totalItems / $itemsPerPage);
//
//        return [
//            'friends' => $paginator->getQuery()->getResult(),
//            'totalPageCount' => $pagesCount,
//            'totalItems' => $totalItems,
//        ];
//    }
}
