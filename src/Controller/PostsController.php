<?php

namespace Sovic\Cms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Cms\Project\ProjectFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/posts/{pageNr}', name: 'posts_index', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function index(int $pageNr): Response
    {
        $response = $this->loadPostIndex($pageNr, self::PER_PAGE);

        return $response ?? $this->render('post/index.html.twig');
    }

    #[Route('/posts/{urlId}', name: 'posts_detail', defaults: [])]
    public function post(string $urlId): Response
    {
        $response = $this->loadPost($urlId);

        return $response ?? $this->render('post/show.html.twig');
    }

    #[Route('/posts/tag/{tagName}/{pageNr}', name: 'posts_w_tag', requirements: ['pageNr' => '\d+'], defaults: ['pageNr' => 1])]
    public function tag(string $tagName, int $pageNr): Response
    {
        $response = $this->loadPostTagIndex($tagName, $pageNr, self::PER_PAGE);

        return $response ?? $this->render('post/index.html.twig');
    }
}
