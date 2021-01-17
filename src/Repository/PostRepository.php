<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Site;
use App\Entity\UserCustomer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findAllByUserSite(UserCustomer $user, Site $site): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.userCustomer = :user')
            ->andWhere('p.site = :site')
            ->setParameter('user', $user)
            ->setParameter('site', $site)
            ->getQuery()
            ->getResult();
    }
    public function findActivePostsBySite(Site $site): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.site = :site')
            ->andWhere('p.featuredParallax = :site')
            ->andWhere('p.isActive = true')
            ->setParameter('site', $site)
            ->getQuery()
            ->getResult();
    }
}
