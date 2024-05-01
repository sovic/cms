<?php

namespace Sovic\Cms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Cms\Project\ProjectFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class PostsController extends BaseController
{
    private const PER_PAGE = 8;

    use PostsControllerTrait;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostFactory            $postFactory,
        PostResultSetFactory   $postResultSetFactory,
        ProjectFactory         $projectFactory,
    ) {
        parent::__construct($entityManager);

        $project = $projectFactory->loadById(1); // TODO
        if (!$project) {
            throw new RuntimeException('Project not found');
        }
        $this->setProject($project);
        $this->setPostFactory($postFactory);
        $this->setPostResultSetFactory($postResultSetFactory);
    }

    #[Route('/stories/p/{pageNr}', name: 'stories_page_redirect', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function storiesPageRedirect(int $pageNr): Response
    {
        return $this->redirectToRoute('stories_index', ['pageNr' => $pageNr], 301);
    }

    #[Route('/posts/p/{pageNr}', name: 'posts_page_redirect', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function postsPageRedirect(int $pageNr): Response
    {
        /** @noinspection PhpRouteMissingInspection */
        return $this->redirectToRoute('blog_index', ['pageNr' => $pageNr], 301);
    }

    #[Route('/stories/{pageNr}', name: 'stories_index', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    #[Route('/posts/{pageNr}', name: 'blog_index', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function index(int $pageNr, Environment $twig, ?string $tagName = null): Response
    {
        $settings = $this->project->getSettings();
        $perPage = $settings->get('posts.per_page') ?? 9;

        if ($tagName) {
            $response = $this->loadPostTagIndex($tagName, $pageNr, $perPage);
        } else {
            $response = $this->loadPostIndex($pageNr, $perPage);
        }
        if ($response !== null) {
            return $response;
        }

        $template = 'post/index.html.twig';
        $projectTemplate = 'projects/' . $this->project->entity->getSlug() . '/post/index.html.twig';
        if ($twig->getLoader()->exists($projectTemplate)) {
            $template = $projectTemplate;
        }

        $this->assignArray($this->project->getSettings()->getTemplateData());

        return $this->render($template);
    }

    #[Route('/stories/tag/{tagName}/{pageNr}', name: 'stories_tag', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    #[Route('/posts/tag/{tagName}/{pageNr}', name: 'posts_tag', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function tag(string $tagName, int $pageNr, Environment $twig): Response
    {
        return $this->index($pageNr, $twig, $tagName);
    }

    #[Route('/posts/{urlId}', name: 'posts_detail', defaults: [])]
    public function post(string $urlId): Response
    {
        $response = $this->loadPost($urlId);

        return $response ?? $this->render('post/show.html.twig');
    }
}
