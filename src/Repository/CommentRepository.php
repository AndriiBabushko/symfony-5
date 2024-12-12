<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * @param array $data
     * @param int $itemsPerPage
     * @param int $page
     * @return arrayå
     */
    public function getAllCommentsByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $queryBuilder = $this->createQueryBuilder('comment');

        if (isset($data['content'])) {
            $queryBuilder->andWhere('comment.content LIKE :content')
                ->setParameter('content', '%' . $data['content'] . '%');
        }

        $queryBuilder->orderBy('comment.createdAt', 'DESC');

        $paginator = new Paginator($queryBuilder);
        $totalItems = count($paginator);
        $pagesCount = (int)ceil($totalItems / $itemsPerPage);

        $queryBuilder->setFirstResult($itemsPerPage * ($page - 1))
            ->setMaxResults($itemsPerPage);

        return [
            'comments' => $queryBuilder->getQuery()->getResult(),
            'totalPageCount' => $pagesCount,
            'totalItems' => $totalItems,
        ];
    }
}
