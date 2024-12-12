<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getAllUsersByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $queryBuilder = $this->createQueryBuilder('user');

        if (!empty($data['username'])) {
            $queryBuilder->andWhere('user.username LIKE :username')
                ->setParameter('username', '%' . $data['username'] . '%');
        }

        if (!empty($data['email'])) {
            $queryBuilder->andWhere('user.email LIKE :email')
                ->setParameter('email', '%' . $data['email'] . '%');
        }

        if (!empty($data['sortBy']) && in_array($data['sortBy'], ['username', 'email', 'createdAt'], true)) {
            $direction = isset($data['sortDirection']) && strtolower($data['sortDirection']) === 'desc' ? 'DESC' : 'ASC';
            $queryBuilder->orderBy('user.' . $data['sortBy'], $direction);
        }

        $paginator = new Paginator($queryBuilder);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $itemsPerPage);

        $paginator->getQuery()
            ->setFirstResult($itemsPerPage * ($page - 1))
            ->setMaxResults($itemsPerPage);

        return [
            'users' => $paginator->getQuery()->getResult(),
            'totalItems' => $totalItems,
            'totalPageCount' => $pagesCount,
        ];
    }
}
