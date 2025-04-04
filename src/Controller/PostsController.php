<?php

namespace Sovic\Cms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Trait\PostsControllerTrait;
use Sovic\Cms\Entity\Author;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Common\Controller\Trait\DownloadTrait;
use Sovic\Common\Pagination\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class PostsController extends ProjectBaseController
{
    private const PER_PAGE = 8;

    use DownloadTrait;
    use PostsControllerTrait;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostFactory            $postFactory,
        PostResultSetFactory   $postResultSetFactory,
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

    #[Route(
        '/posts/{pageNr}',
        name: 'posts_index',
        requirements: ['pageNr' => '\d+'],
        defaults: ['pageNr' => 1]
    )]
    public function index(int $pageNr): Response
    {
        $perPage = $this->settings->get('posts', 'per_page', 9);

        $response = $this->loadPostIndex($pageNr, $perPage);

        $this->assign('active_item', '/posts');

        return $response ?? $this->render($this->getProjectTemplatePath('post/index'));
    }

    #[Route(
        '/posts/tag/{tagName}/{pageNr}',
        name: 'posts_tag',
        requirements: ['pageNr' => '\d+'],
        defaults: ['pageNr' => 1]
    )]
    public function tag(string $tagName, int $pageNr): Response
    {
        $perPage = $this->settings->get('posts', 'per_page', 9);

        $response = $this->loadPostTagIndex($tagName, $pageNr, $perPage);

        $this->assign('active_item', '/posts');

        return $response ?? $this->render($this->getProjectTemplatePath('post/index'));
    }

    #[Route(
        '/posts/archive/{year}/{month}/{pageNr}',
        name: 'posts_monthly_archive',
        requirements: ['year' => '\d{4}', 'month' => '\d{2}', 'pageNr' => '\d+'],
        defaults: ['pageNr' => 1]
    )]
    public function monthlyArchive(int $year, int $month, int $pageNr): Response
    {
        $perPage = $this->settings->get('posts', 'per_page', 9);

        $this->loadMonthlyArchive($year, $month, $pageNr, $perPage);

        $this->assign('active_item', '/posts');

        return $this->render($this->getProjectTemplatePath('post/index'));
    }

    #[Route('/posts/{urlId}', name: 'posts_detail', defaults: [])]
    public function post(string $urlId, Request $request, RouterInterface $router): Response
    {
        $response = $this->loadPost($urlId);
        if ($response !== null) {
            return $response;
        }

        $downloads = [];
        $gallery = $this->getGallery('downloads');
        if ($gallery) {
            $downloads = $gallery->getItems();
            foreach ($downloads as &$download) {
                $download['url'] = $router->generate('gallery_item_download', ['id' => $download['id']]);
            }
            unset($download);
        }

        $gallery = $this->getGallery('post');
        $cover = $gallery?->getCoverImage();
        if ($cover) {
            // TODO add gallery->setBaseUrl() method
            $baseUrl = $this->settings->get('gallery', 'base_url');
            $cover['small'] = $baseUrl . '/' . $cover['small'];
            $cover['big'] = $baseUrl . '/' . $cover['big'];
            $cover['full'] = $baseUrl . '/' . $cover['full'];
        }

        $secret = $request->get('secret');
        $isGalleryDownloadEnabled = $gallery->entity->isDownloadEnabled()
            && $secret === $this->post->entity->getSecret();
        $galleryDownloadUrl = $router->generate('gallery_download', ['id' => $gallery->getId()])
            . '?secret=' . $this->post->entity->getSecret();

        $this->assign('active_item', '/posts');
        $this->assign('cover', $cover);
        $this->assign('downloads', $downloads);
        $this->assign('has_parallax', $cover !== null);
        $this->assign('is_gallery_download_enabled', $isGalleryDownloadEnabled);
        $this->assign('gallery_download_url', $galleryDownloadUrl);

        return $this->render($this->getProjectTemplatePath('post/detail'));
    }

    #[Route('/authors/{pageNr}', name: 'authors', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function authors(int $pageNr, PostResultSetFactory $postResultSetFactory): Response
    {
        $perPage = $this->settings->get('posts', 'per_page', 24);

        $total = $this->entityManager
            ->getRepository(Author::class)
            ->count(['project' => $this->project->entity]);

        $pagination = new Pagination($total, $perPage);
        if ($pageNr > $pagination->getPageCount()) {
            return $this->renderProject404();
        }
        $pagination->setCurrentPage($pageNr);

        $authors = $this->entityManager
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
