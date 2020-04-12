<?php

namespace App\Repository;

use App\Document\Post;
use App\Document\Site;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\PHPCR\Query\Query;

class PostRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        parent::__construct($dm, $dm->getUnitOfWork(), $dm->getClassMetadata(Post::class));
    }

    public function findActivePosts(Site $site, $limit = false)
    {
        $qb = $this->createQueryBuilder()
            ->field('featuredParallax')->notEqual(true)
            ->field('active')->equals(true)
            ->field('site')->equals($site)
            ->sort('createdAt', 'DESC');

        if ($limit) {
            $qb->limit($limit);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $qb->getQuery()->execute();
    }

    public function findReadMorePosts(Site $site): array
    {
        $posts = $this->findActivePosts($site, 2);

        $allHaveImages = true;
        /** @var Post $post */
        foreach ($posts as $post) {
            if (null === $post->getDefaultImage()) {
                $allHaveImages = false;
                break;
            }
        }

        return ['posts' => $posts, 'allHaveImages' => $allHaveImages];
    }

    public function findActiveByUserSite(User $user, Site $site)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->dm->createQueryBuilder(Post::class)
            ->field('user')->equals($user)
            ->field('site')->equals($site)
            ->field('active')->equals(true)
            ->field('deleted')->notEqual(true)
            ->getQuery()->execute();
    }

    public function findActiveBySlug(string $slug, Site $site)
    {
        return $this->dm->createQueryBuilder(Post::class)
            ->field('site')->equals($site)
            ->field('slug')->equals($slug)
            ->field('active')->equals(true)
            ->field('deleted')->notEqual(true)
            ->getQuery()
            ->getSingleResult();
    }
}
