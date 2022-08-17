<?php

namespace SovicCms\Controller;

use SovicCms\Entity\Post as PostEntity;
use SovicCms\Entity\Tag;
use SovicCms\Helpers\Pagination;
use SovicCms\Post\Post;
use SovicCms\Post\PostFactory;
use SovicCms\Post\PostResultSetFactory;
use SovicCms\Repository\PostRepository;

/**
 * Requires PostFactory, PostResultSetFactory
 */
trait PostsControllerTrait
{
    private PostFactory $postFactory;
    private PostResultSetFactory $postResultSetFactory;

    private ?Post $post = null;
    private ?Tag $tag = null;
    private ?string $postsMediaBaseUrl = null;

    public function setPostsMediaBaseUrl(?string $postsMediaBaseUrl): void
    {
        $this->postsMediaBaseUrl = $postsMediaBaseUrl;
    }

    public function setPostFactory(PostFactory $postFactory): void
    {
        $this->postFactory = $postFactory;
    }

    public function setPostResultSetFactory(PostResultSetFactory $postResultSetFactory): void
    {
        $this->postResultSetFactory = $postResultSetFactory;
    }

    protected function getPost(): ?Post
    {
        return $this->post;
    }

    protected function getTag(): ?Tag
    {
        return $this->tag;
    }

    protected function loadPostIndex(int $pageNr, int $perPage): void
    {
        /** @var PostRepository $repo */
        $repo = $this->getEntityManager()->getRepository(PostEntity::class);
        $posts = $repo->findPublic($perPage, ($pageNr - 1) * $perPage);
        $postsResultSet = $this->postResultSetFactory->createFromEntities($posts);

        $pagination = new Pagination($repo->countPublic(), $perPage);
        $pagination->setCurrentPage($pageNr);

        $this->assign('media_base_url', $this->postsMediaBaseUrl);
        $this->assign('pagination', $pagination);
        $this->assign('posts', $postsResultSet->toArray());
    }

    protected function loadPostTagIndex(string $tagName, int $pageNr, int $perPage): void
    {
        $tag = $this->getEntityManager()->getRepository(Tag::class)->findOneBy(['urlId' => $tagName]);
        if (null === $tag) {
            return;
        }
        $this->tag = $tag;

        /** @var PostRepository $repo */
        $repo = $this->getEntityManager()->getRepository(PostEntity::class);
        $posts = $repo->findPublicByTag($tag, $perPage, ($pageNr - 1) * $perPage);
        $postsResultSet = $this->postResultSetFactory->createFromEntities($posts);

        $pagination = new Pagination($repo->countPublicByTag($tag), self::PER_PAGE);
        $pagination->setCurrentPage($pageNr);

        $this->assign('media_base_url', $this->postsMediaBaseUrl);
        $this->assign('pagination', $pagination);
        $this->assign('posts', $postsResultSet->toArray());
        $this->assign('tag', $tag);
    }

    protected function loadPost(string $urlId): void
    {
        $post = $this->postFactory->loadByUrlId($urlId, true);
        if (null === $post) {
            return;
        }
        $this->post = $post;
        $authors = $this->post->getAuthors();
        $galleryManager = $post->getGalleryManager();
        if ($this->postsMediaBaseUrl) {
            $galleryManager->setBaseUrl($this->postsMediaBaseUrl);
        }
        $media = $galleryManager->getGallery('post');

        $this->assign('authors', $authors);
        $this->assign('media', $media);
        $this->assign('media_base_url', $this->postsMediaBaseUrl);
        $this->assign('post', $post);
    }
}
