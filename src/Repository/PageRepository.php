<?php

namespace App\Repository;

use App\Entity\Page;
use App\Entity\Site;
use App\Entity\UserCustomer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    // /**
    //  * @return Page[] Returns an array of Page objects
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

    /*
    public function findOneBySomeField($value): ?Page
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findActiveByUserSite(UserCustomer $user, Site $site)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.userCustomer = :user')
            ->andWhere('p.isDeleted = false OR p.isDeleted IS NULL')
            ->andWhere('p.site = :site')
            ->setParameter('user', $user)
            ->setParameter('site', $site)
            ->orderBy('p.sequenceOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllActiveBySite(Site $site)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->andWhere('p.site = :site')
            ->andWhere('p.isDeleted = false OR p.isDeleted IS NULL')
            ->andWhere('p.isActive = true')
            ->setParameter('site', $site)
            ->orderBy('p.sequenceOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveBySlug(string $slug, Site $site)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->andWhere('p.site = :site')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.isDeleted = false OR p.isDeleted IS NULL')
            ->andWhere('p.isActive = true')
            ->setParameter('site', $site)
            ->setParameter('slug', $slug)
            ->orderBy('p.sequenceOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByIdsAndUser(array $ids, UserCustomer $user)
    {
        $qb = $this->createQueryBuilder('p');

            return $qb
            ->andWhere('p.userCustomer = :user')
            ->andWhere($qb->expr()->in('p.id', $ids))
            ->setParameter('user', $user)
            ->orderBy('p.sequenceOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function deleteAllBySite(Site $site):void {
        $qb = $this->createQueryBuilder('p');
        $deleteQuery = $qb->delete('App:Page', 'p')->where('p.site = :siteId')->setParameter('siteId', $site->getId())->getQuery();
        $deleteQuery->execute();
    }
}
