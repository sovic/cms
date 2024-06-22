<?php

namespace Sovic\Cms\Controller;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Controller\Trait\PostsControllerTrait;
use Sovic\Cms\Controller\Trait\ProjectControllerTrait;
use Sovic\Cms\Entity\Author;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Common\Controller\Trait\DownloadTrait;
use Sovic\Common\Pagination\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostsController extends BaseController implements ProjectControllerInterface
{
    private const PER_PAGE = 8;

    use DownloadTrait;
    use PostsControllerTrait;
    use ProjectControllerTrait;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostFactory            $postFactory,
        PostResultSetFactory   $postResultSetFactory
    ) {
        parent::__construct($entityManager);

        $this->setPostFactory($postFactory);
        $this->setPostResultSetFactory($postResultSetFactory);
    }

    #[Route('/posts/p/{pageNr}', name: 'posts_page_redirect', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function postsPageRedirect(int $pageNr): Response
    {
        return $this->redirectToRoute('posts_index', ['pageNr' => $pageNr], 301);
    }

    #[Route('/posts/{pageNr}', name: 'posts_index', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function index(int $pageNr, ?string $tagName = null): Response
    {
        $project = $this->project;
        $settings = $project->getSettings();
        $perPage = $settings->get('posts.per_page') ?? 9;

        if ($tagName) {
            $response = $this->loadPostTagIndex($tagName, $pageNr, $perPage);
        } else {
            $response = $this->loadPostIndex($pageNr, $perPage);
        }

        $this->assign('active_item', '/posts');

        return $response ?? $this->render($this->getProjectTemplatePath('post/index'));
    }

    #[Route('/posts/tag/{tagName}/{pageNr}', name: 'posts_tag', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function tag(string $tagName, int $pageNr): Response
    {
        return $this->index($pageNr, $tagName);
    }

    #[Route(
        '/posts/archive/{year}/{month}/{pageNr}',
        name: 'posts_monthly_archive',
        requirements: ['year' => '\d{4}', 'month' => '\d{2}', 'pageNr' => '\d+'],
        defaults: ['pageNr' => 1]
    )]
    public function monthlyArchive(int $year, int $month, int $pageNr): Response
    {
        $project = $this->project;
        $settings = $project->getSettings();
        $perPage = $settings->get('posts.per_page') ?? 9;

        $this->loadMonthlyArchive($year, $month, $pageNr, $perPage);

        $this->assign('active_item', '/posts');

        return $this->render($this->getProjectTemplatePath('post/index'));
    }

    #[Route('/posts/{urlId}', name: 'posts_detail', defaults: [])]
    public function post(string $urlId, Request $request): Response
    {
        $response = $this->loadPost($urlId);
        if ($response !== null) {
            return $response;
        }
        $secret = $request->get('secret');
        if (!$this->isAccessEnabled($secret)) {
            return $this->renderProject404();
        }

        $downloads = [];
        $gallery = $this->getGallery('downloads');
        if ($gallery) {
            $downloads = $gallery->getItems();
        }

        $project = $this->project;
        $settings = $project->getSettings();

        $gallery = $this->getGallery('post');
        $cover = $gallery?->getCoverImage();
        if ($cover) {
            // TODO add gallery->setBaseUrl() method
            $baseUrl = $settings->get('gallery.base_url');
            $cover['small'] = $baseUrl . '/' . $cover['small'];
            $cover['big'] = $baseUrl . '/' . $cover['big'];
            $cover['full'] = $baseUrl . '/' . $cover['full'];
        }

        $this->assign('active_item', '/posts');
        $this->assign('cover', $cover);
        $this->assign('downloads', $downloads);
        $this->assign('has_parallax', $cover !== null);
        $this->assign('is_download_enabled', $this->isGalleryDownloadEnabled($secret));
        $this->assign('secret', $secret); // for download url
        $this->assign('url_id', $urlId); // for download url

        return $this->render($this->getProjectTemplatePath('post/detail'));
    }

    #[Route('/posts/{urlId}/download-gallery/{secret}', name: 'posts_download_gallery', defaults: [])]
    public function downloadGallery(string $urlId, string $secret, FilesystemOperator $galleryStorage): Response
    {
        $response = $this->loadPost($urlId);
        if ($response !== null) {
            return $response;
        }

        if (!$this->isGalleryDownloadEnabled($secret)) {
            return $this->renderProject404();
        }

        $gallery = $this->getGallery('post');
        if (!$gallery) {
            return $this->renderProject404();
        }

        $gallery->setFilesystemOperator($galleryStorage);
        try {
            $archivePath = $gallery->createZipArchive();

            $slugify = new Slugify();
            $slugify->activateRuleSet('default');
            $fileName = $slugify->slugify($this->post->entity->getName()) . '.zip';

            $this->download($archivePath, $fileName);
        } catch (FilesystemException) {
            return $this->renderProject404();
        }
    }

    #[Route('/authors/{pageNr}', name: 'authors', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function authors(int $pageNr, PostResultSetFactory $postResultSetFactory): Response
    {
        $project = $this->project;
        $settings = $project->getSettings();
        $perPage = $settings->get('posts.per_page') ?? 24;

        $total = $this
            ->getEntityManager()
            ->getRepository(Author::class)
            ->count(['project' => $this->project->entity]);

        $pagination = new Pagination($total, $perPage);
        if ($pageNr > $pagination->getPageCount()) {
            return $this->renderProject404();
        }
        $pagination->setCurrentPage($pageNr);

        $authors = $this
            ->getEntityManager()
            ->getRepository(Author::class)
            ->findBy(
                [
                    'project' => $this->project->entity,
                ],
                ['surname' => 'ASC'],
                $perPage,
                ($pageNr - 1) * $perPage,
            );

        $prs = $postResultSetFactory->loadByAuthors($authors);

        $this->assign('authors', $authors);
        $this->assign('pagination', $pagination);
        $this->assign('posts_by_authors', $prs->toArray());

        return $this->render($this->getProjectTemplatePath('post/authors'));
    }

    #[Route('/posts/search', name: 'posts_search', priority: 1)]
    public function search(): Response
    {
        return $this->render($this->getProjectTemplatePath('post/search'));
    }
}
