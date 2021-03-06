<?php

namespace App\Controller;

use App\Controller\Admin\UploadController;
use App\Entity\Album;
use App\Entity\Site;
use App\Repository\AlbumRepository;
use App\Repository\DomainRepository;
use App\Repository\FileRepository;
use App\Repository\PageRepository;
use App\Repository\SiteRepository;
use App\Service\Domain\DomainResolver;
use App\Service\Site\LayoutResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlbumController extends AbstractController
{
    private $domainResolver;
    private $layoutResolver;
    private $entityManager;

    public function __construct(
        DomainResolver $domainResolver,
        LayoutResolver $layoutResolver,
        EntityManagerInterface $entityManager
    ) {
        $this->domainResolver = $domainResolver;
        $this->layoutResolver = $layoutResolver;
        $this->entityManager = $entityManager;
    }

    public function view(
        Request $request,
        SiteRepository $siteRepository,
        DomainRepository $domainRepository,
        PageRepository $pageRepository,
        AlbumRepository $albumRepository,
        FileRepository $fileRepository,
        ParameterBagInterface $params,
        string $slug
    ): Response {

        /** @var Site $site */
        $site = $siteRepository->findOneBy(['host' => $this->domainResolver->extractDomainFromHost($request->getHost())]);

        $domain = $domainRepository->findOneBy(['name' => $this->domainResolver->extractDomainFromHost($request->getHost())]);
        if (null === $site && null !== $domain) {
            $site = $siteRepository->findOneBy(['domain' => $domain]);
        }

        if (null === $site) {
            throw new NotFoundHttpException();
        }

        /** @var Album $album */
        $album = $albumRepository->findOneBy(['isActive' => true, 'slug' => $slug, 'site' => $site]);
        $pages = $pageRepository->findAllActiveBySite($site);

        if (null === $album) {
            throw new NotFoundHttpException();
        }

        return $this->render(
            'UserSite/PhotographySite/minimal/album.html.twig',
            [
                'site' => $site,
                'slug' => $slug,
                'templateCss' => $this->layoutResolver->getSiteTemplateCss($site),
                'pages' => $pages,
                'album' => $album,
                'files' => UploadController::getOrderedFiles($fileRepository->findAllActiveByAlbumAndSite($album, $site)),
                'layout' => $this->layoutResolver->getLayout($site),
            ]
        );
    }
}
