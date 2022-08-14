<?php

namespace SovicCms\Controller;

use SovicCms\Entity\Post as PostEntity;
use SovicCms\Entity\Tag;
use SovicCms\Helpers\Pagination;
use SovicCms\Post\Post;
use SovicCms\Repository\PostRepository;

trait PostsControllerTrait
{
    private ?Post $post = null;
    private ?Tag $tag = null;
    private ?string $postsMediaBaseUrl = null;

    public function setPostsMediaBaseUrl(?string $postsMediaBaseUrl): void
    {
        $this->postsMediaBaseUrl = $postsMediaBaseUrl;
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
        $this->assign('posts', $postsResultSet->toArray());
        $this->assign('pagination', $pagination);
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
        $this->assign('posts', $postsResultSet->toArray());
        $this->assign('pagination', $pagination);
        $this->assign('tag', $tag);
    }

    protected function loadPost(string $urlId): void
    {
        $post = $this->postFactory->loadByUrlId($urlId, true);
        if (null === $post) {
            return;
        }
        $this->post = $post;
        $galleryManager = $post->getGalleryManager();
        if ($this->postsMediaBaseUrl) {
            $galleryManager->setBaseUrl($this->postsMediaBaseUrl);
        }
        $media = $galleryManager->getGallery('post');

        $this->assign('media', $media);
        $this->assign('post', $post);
    }
}
