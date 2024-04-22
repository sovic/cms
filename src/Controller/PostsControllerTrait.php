<?php

namespace Sovic\Cms\Controller;

use Sovic\Cms\Entity\Post as PostEntity;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Helpers\Pagination;
use Sovic\Cms\Post\Post;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Cms\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires PostFactory, PostResultSetFactory
 */
trait PostsControllerTrait
{
    private PostFactory $postFactory;
    private PostResultSetFactory $postResultSetFactory;

    private ?Post $post = null;
    private ?Tag $tag = null;

    private ?string $postGalleryBaseUrl = null;
    private bool $addAuthors = false;
    private bool $addCovers = true;

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

    public function setPostGalleryBaseUrl(?string $postGalleryBaseUrl): void
    {
        $this->postGalleryBaseUrl = $postGalleryBaseUrl;
    }

    public function setAddAuthors(bool $addAuthors): void
    {
        $this->addAuthors = $addAuthors;
    }

    protected function loadPostIndex(int $pageNr, int $perPage): ?Response
    {
        /** @var PostRepository $repo */
        $repo = $this->getEntityManager()->getRepository(PostEntity::class);
        $posts = $repo->findPublic($perPage, ($pageNr - 1) * $perPage);
        $postsResultSet = $this->postResultSetFactory->createFromEntities($posts);
        $postsResultSet->setAddAuthors($this->addAuthors);
        $postsResultSet->setAddCovers($this->addCovers);
        if ($this->postGalleryBaseUrl) {
            $postsResultSet->setGalleryBaseUrl($this->postGalleryBaseUrl);
        }

        $pagination = new Pagination($repo->countPublic(), $perPage);
        if ($pageNr > $pagination->getPageCount()) {
            return $this->show404();
        }
        $pagination->setCurrentPage($pageNr);

        $this->assign('pagination', $pagination);
        $this->assign('post_gallery_base_url', $this->postGalleryBaseUrl);
        $this->assign('posts', $postsResultSet->toArray());

        return null;
    }

    protected function loadPostTagIndex(string $tagName, int $pageNr, int $perPage): ?Response
    {
        $tag = $this->getEntityManager()->getRepository(Tag::class)->findOneBy(['urlId' => $tagName]);
        if (null === $tag) {
            return $this->show404();
        }
        $this->tag = $tag;

        /** @var PostRepository $repo */
        $repo = $this->getEntityManager()->getRepository(PostEntity::class);
        $posts = $repo->findPublicByTag($tag, $perPage, ($pageNr - 1) * $perPage);
        $postsResultSet = $this->postResultSetFactory->createFromEntities($posts);
        $postsResultSet->setAddAuthors($this->addAuthors);
        $postsResultSet->setAddCovers($this->addCovers);
        if ($this->postGalleryBaseUrl) {
            $postsResultSet->setGalleryBaseUrl($this->postGalleryBaseUrl);
        }

        $pagination = new Pagination($repo->countPublicByTag($tag), $perPage);
        $pagination->setCurrentPage($pageNr);

        $this->assign('pagination', $pagination);
        $this->assign('post_gallery_base_url', $this->postGalleryBaseUrl);
        $this->assign('posts', $postsResultSet->toArray());
        $this->assign('tag', $tag);

        return null;
    }

    protected function loadPost(string $urlId): ?Response
    {
        $post = $this->postFactory->loadByUrlId($urlId, true);
        if (null === $post) {
            return $this->show404();
        }
        $this->post = $post;

        // galleries
        $galleryManager = $this->post->getGalleryManager();
        $gallery = $galleryManager->loadGallery('post');
        $resultSet = $gallery->getItemsResultSet();
        if ($this->postGalleryBaseUrl) {
            $resultSet->setBaseUrl($this->postGalleryBaseUrl);
        }

        $this->assign('post', $post);
        $this->assign('post_authors', $this->post->getAuthors());
        $this->assign('post_gallery', $resultSet->toArray());

        return null;
    }
}
