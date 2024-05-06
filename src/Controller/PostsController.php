<?php

namespace Sovic\Cms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\Author;
use Sovic\Cms\Helpers\Pagination;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostsController extends BaseController implements ProjectControllerInterface
{
    private const PER_PAGE = 8;

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
        /** @noinspection PhpRouteMissingInspection */
        return $this->redirectToRoute('blog_index', ['pageNr' => $pageNr], 301);
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

        return $response ?? $this->render($this->getProjectTemplatePath('post/index'));
    }

    #[Route('/posts/tag/{tagName}/{pageNr}', name: 'posts_tag', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function tag(string $tagName, int $pageNr): Response
    {
        return $this->index($pageNr, $tagName);
    }

    #[Route('/posts/{urlId}', name: 'posts_detail', defaults: [])]
    public function post(string $urlId, Request $request): Response
    {
        $response = $this->loadPost($urlId);
        if ($response !== null) {
            return $response;
        }
        $post = $this->getPost();
        if (null === $post) {
            return $this->render404();
        }

        $secret = $request->get('secret');
        $isAuthorized = !empty($secret) && $secret === $post->entity->getSecret();
        if (!$isAuthorized && !$post->entity->isPublic()) {
            return $this->render404();
        }

        $downloads = [];
        if ($isAuthorized) {
            $gallery = $post->getGalleryManager()->getGallery('downloads');
            if ($gallery) {
                $downloads = $gallery->getItems();
            }
        }

        $project = $this->project;
        $settings = $project->getSettings();

        $cover = $post->getGalleryManager()->getGallery('post')?->getCoverImage();
        if ($cover) {
            // TODO add gallery->setBaseUrl() method
            $baseUrl = $settings->get('gallery.base_url');
            $cover['small'] = $baseUrl . '/' . $cover['small'];
            $cover['big'] = $baseUrl . '/' . $cover['big'];
            $cover['full'] = $baseUrl . '/' . $cover['full'];
        }

        $this->assign('cover', $cover);
        $this->assign('downloads', $downloads);
        $this->assign('has_parallax', $cover !== null);

        return $this->render($this->getProjectTemplatePath('post/detail'));
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
            return $this->render404();
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
