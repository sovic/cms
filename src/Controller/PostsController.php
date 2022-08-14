<?php

namespace SovicCms\Controller;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use SovicCms\Post\PostFactory;
use SovicCms\Post\PostResultSetFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends FrontendController
{
    private const PER_PAGE = 8;

    use PostsControllerTrait;

    private PostFactory $postFactory;
    private PostResultSetFactory $postResultSetFactory;

    #[Pure]
    public function __construct(
        EntityManagerInterface $entityManager,
        PostFactory            $postFactory,
        PostResultSetFactory   $postResultSetFactory
    ) {
        parent::__construct($entityManager);
        $this->postFactory = $postFactory;
        $this->postResultSetFactory = $postResultSetFactory;
    }

    /**
     * @Route(
     *     "/posts/{pageNr}",
     *     name="posts_index",
     *     requirements={"pageNr"="\d+"},
     *     defaults={"pageNr"=1}
     * )
     *
     * @param int $pageNr
     * @return Response
     */
    public function index(int $pageNr): Response
    {
        $this->loadPostIndex($pageNr, self::PER_PAGE);

        return $this->render('post/index.html.twig');
    }

    /**
     * @Route("/posts/{urlId}", name="posts_detail", defaults={})
     *
     * @param string $urlId
     * @return Response
     */
    public function post(string $urlId): Response
    {
        $this->loadPost($urlId);
        if (null === $this->post) {
            return $this->show404();
        }

        return $this->render('post/detail.html.twig');
    }

    /**
     * @Route(
     *     "/posts/tag/{tagName}/{pageNr}",
     *     name="posts_w_tag",
     *     requirements={"pageNr"="\d+"},
     *     defaults={"pageNr"=1}
     * )
     *
     * @param string $tagName
     * @param int $pageNr
     * @return Response
     */
    public function tag(string $tagName, int $pageNr): Response
    {
        $this->loadPostTagIndex($tagName, $pageNr, self::PER_PAGE);
        if (null === $this->tag) {
            return $this->show404();
        }

        return $this->render('post/index.html.twig');
    }
}
