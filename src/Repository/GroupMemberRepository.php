<?php
//
//namespace App\Repository;
//
//use App\Entity\GroupMember;
//use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
//use Doctrine\ORM\Tools\Pagination\Paginator;
//use Doctrine\Persistence\ManagerRegistry;
//
///**
// * @extends ServiceEntityRepository<GroupMember>
// */
//class GroupMemberRepository extends ServiceEntityRepository
//{
//    public function __construct(ManagerRegistry $registry)
//    {
//        parent::__construct($registry, GroupMember::class);
//    }
//
//    public function getAllGroupMembersByFilter(array $data, int $itemsPerPage, int $page): array
//    {
//        $queryBuilder = $this->createQueryBuilder('gm');
//
//        if (isset($data['groupId'])) {
//            $queryBuilder->andWhere('gm.group = :groupId')
//                ->setParameter('groupId', $data['groupId']);
//        }
//
//        if (isset($data['userId'])) {
//            $queryBuilder->andWhere('gm.user = :userId')
//                ->setParameter('userId', $data['userId']);
//        }
//
//        if (isset($data['role'])) {
//            $queryBuilder->andWhere('gm.role = :role')
//                ->setParameter('role', $data['role']);
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
//            'groupMembers' => $paginator->getQuery()->getResult(),
//            'totalPageCount' => $pagesCount,
//            'totalItems' => $totalItems,
//        ];
//    }
//}
