<?php
//
//namespace App\Repository;
//
//use App\Entity\Group;
//use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
//use Doctrine\ORM\Tools\Pagination\Paginator;
//use Doctrine\Persistence\ManagerRegistry;
//
///**
// * @extends ServiceEntityRepository<Group>
// */
//class GroupRepository extends ServiceEntityRepository
//{
//    public function __construct(ManagerRegistry $registry)
//    {
//        parent::__construct($registry, Group::class);
//    }
//
//    /**
//     * @param array $data
//     * @param int $itemsPerPage
//     * @param int $page
//     * @return array
//     */
//    public function getAllGroupsByFilter(array $data, int $itemsPerPage, int $page): array
//    {
//        $queryBuilder = $this->createQueryBuilder('g');
//
//        if (isset($data['name'])) {
//            $queryBuilder->andWhere('g.name LIKE :name')
//                ->setParameter('name', '%' . $data['name'] . '%');
//        }
//
//        if (isset($data['creatorId'])) {
//            $queryBuilder->andWhere('g.creator = :creatorId')
//                ->setParameter('creatorId', $data['creatorId']);
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
//            'groups' => $paginator->getQuery()->getResult(),
//            'totalPageCount' => $pagesCount,
//            'totalItems' => $totalItems,
//        ];
//    }
//}
