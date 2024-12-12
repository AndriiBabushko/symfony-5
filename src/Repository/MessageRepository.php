<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @param array $data
     * @param int $itemsPerPage
     * @param int $page
     * @return array
     */
    public function getAllMessagesByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $queryBuilder = $this->createQueryBuilder('message');

        if (isset($data['content'])) {
            $queryBuilder->andWhere('message.content LIKE :content')
                ->setParameter('content', '%' . $data['content'] . '%');
        }

        if (isset($data['senderId'])) {
            $queryBuilder->andWhere('message.sender = :senderId')
                ->setParameter('senderId', $data['senderId']);
        }

        if (isset($data['receiverId'])) {
            $queryBuilder->andWhere('message.receiver = :receiverId')
                ->setParameter('receiverId', $data['receiverId']);
        }

        $queryBuilder->setFirstResult($itemsPerPage * ($page - 1))
            ->setMaxResults($itemsPerPage);

        $paginator = new Paginator($queryBuilder);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $itemsPerPage);

        return [
            'messages' => $paginator->getQuery()->getResult(),
            'totalPageCount' => $pagesCount,
            'totalItems' => $totalItems,
        ];
    }
}
