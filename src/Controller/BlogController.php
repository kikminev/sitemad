<?php

namespace App\Controller;

use App\Document\File;
use App\Document\Message;
use App\Document\Post;
use App\Form\ContactType;
use App\Repository\DomainRepository;
use App\Repository\PageRepository;
use App\Repository\PostRepository;
use App\Repository\SiteRepository;
use App\Service\Domain\DomainResolver;
use App\Service\Site\LayoutResolver;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Document\Site;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends AbstractController
{
    private $domainResolver;
    private $layoutResolver;

    public function __construct(DomainResolver $domainResolver, LayoutResolver $layoutResolver)
    {
        $this->domainResolver = $domainResolver;
        $this->layoutResolver = $layoutResolver;
    }

    public function list(
        Request $request,
        PageRepository $pageRepository,
        SiteRepository $siteRepository,
        DomainRepository $domainRepository,
        PostRepository $postRepository
    ) {
        /** @var Site $site */
        $site = $siteRepository->findOneBy(['host' => $this->domainResolver->extractDomainFromHost($request->getHost())]);
        $domain = $domainRepository->findOneBy(['name' => $this->domainResolver->extractDomainFromHost($request->getHost())]);
        if (null === $site && null !== $domain) {
            $site = $siteRepository->findOneBy(['domain' => $domain]);
        }

        if (null === $site) {
            throw new NotFoundHttpException();
        }

        $posts = $postRepository->findBy(['site' => $site, 'active' => true]);
        $pages = $pageRepository->findBy(['site' => $site], ['order' => 'ASC']);
        $form = $this->createForm(ContactType::class, new Message(), ['action' => $this->generateUrl('user_site_contact')]);

        // todo: pagination
        return $this->render(
            'UserSite/BlogSite/list.html.twig',
            [
                'site' => $site,
                'pages' => $pages,
                'slug' => 'blog',
                'posts' => $posts,
                'templateCss' => $this->layoutResolver->getSiteTemplateCss($site),
                'form' => $form->createView(),
                'layout' => $this->layoutResolver->getLayout($site),
            ]
        );
    }

    /**
     * @param Request $request
     * @param $slug
     * @param PostRepository $postRepository
     * @param SiteRepository $siteRepository
     * @param PageRepository $pageRepository
     * @param DomainRepository $domainRepository
     * @return Response
     * @throws MongoDBException
     */
    public function view(
        Request $request,
        $slug,
        PostRepository $postRepository,
        SiteRepository $siteRepository,
        PageRepository $pageRepository,
        DomainRepository $domainRepository
    ): Response {

        // todo: fix this if and extract to a normal logic
        /** @var Site $site */
        $site = $siteRepository->findOneBy(['host' => $this->domainResolver->extractDomainFromHost($request->getHost())]);

        $domain = $domainRepository->findOneBy(['name' => $this->domainResolver->extractDomainFromHost($request->getHost())]);
        if (null === $site && null !== $domain) {
            $site = $siteRepository->findOneBy(['domain' => $domain]);
        }

        if (null === $site) {
            throw new NotFoundHttpException();
        }

        /** @var Post $post */
        $post = $postRepository->findActiveBySlug($slug, $site);
        $page = $pageRepository->findActiveBySlug(!empty($slug) ? $slug : 'home', $site);
        $pages = $pageRepository->findActiveBySite($site);
        $form = $this->createForm(ContactType::class, new Message(), ['action' => $this->generateUrl('user_site_contact')]);
        $morePosts = $postRepository->findReadMorePosts($site);

        if (null === $post) {
            throw new NotFoundHttpException();
        }

        return $this->render(
            'UserSite/BlogSite/post.html.twig',
            [
                'site' => $site,
                'post' => $post,
                'slug' => $slug,
                'page' => $page,
                'pages' => $pages,
                'morePosts' => $morePosts,
                'templateCss' => $this->layoutResolver->getSiteTemplateCss($site),
                'files' => $post->getFiles(),
                'form' => $form->createView(),
                'layout' => $this->layoutResolver->getLayout($site),
            ]
        );
    }
}
