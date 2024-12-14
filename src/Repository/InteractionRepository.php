<?php
//
//namespace App\Repository;
//
//use App\Entity\Interaction;
//use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
//use Doctrine\ORM\Tools\Pagination\Paginator;
//use Doctrine\Persistence\ManagerRegistry;
//
///**
// * @extends ServiceEntityRepository<Interaction>
// */
//class InteractionRepository extends ServiceEntityRepository
//{
//    public function __construct(ManagerRegistry $registry)
//    {
//        parent::__construct($registry, Interaction::class);
//    }
//
//    public function getAllInteractionsByFilter(array $data, int $itemsPerPage, int $page): array
//    {
//        $queryBuilder = $this->createQueryBuilder('i');
//
//        if (isset($data['interactionType'])) {
//            $queryBuilder->andWhere('i.interactionType = :interactionType')
//                ->setParameter('interactionType', $data['interactionType']);
//        }
//
//        if (isset($data['userId'])) {
//            $queryBuilder->andWhere('i.user = :userId')
//                ->setParameter('userId', $data['userId']);
//        }
//
//        if (isset($data['targetId'])) {
//            $queryBuilder->andWhere('i.targetId = :targetId')
//                ->setParameter('targetId', $data['targetId']);
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
//            'interactions' => $paginator->getQuery()->getResult(),
//            'totalPageCount' => $pagesCount,
//            'totalItems' => $totalItems,
//        ];
//    }
//}
