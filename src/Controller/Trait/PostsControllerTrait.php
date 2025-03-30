<?php

namespace Sovic\Cms\Controller\Trait;

use LogicException;
use Sovic\Cms\Entity\Post as PostEntity;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Post\Post;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Cms\Post\PostSearchRequest;
use Sovic\Cms\Repository\PostRepository;
use Sovic\Common\Helpers\Date;
use Sovic\Common\Pagination\Pagination;
use Sovic\Gallery\Gallery\Gallery;
use Symfony\Component\HttpFoundation\Response;

trait PostsControllerTrait
{
    private PostFactory $postFactory;
    private PostResultSetFactory $postResultSetFactory;

    private ?Post $post = null;
    private ?Tag $tag = null;

    private bool $addAuthors = true;
    private bool $addCovers = true;

    /** @var Gallery[] */
    private array $galleries = [];

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

    public function setAddAuthors(bool $addAuthors): void
    {
        $this->addAuthors = $addAuthors;
    }

    protected function loadPostIndex(int $pageNr, int $perPage): ?Response
    {
        /** @var PostRepository $repo */
        $repo = $this->entityManager->getRepository(PostEntity::class);

        $pagination = new Pagination($repo->countPublic($this->project), $perPage);
        if ($pageNr > $pagination->getPageCount()) {
            return $this->renderProject404();
        }
        $pagination->setCurrentPage($pageNr);

        $posts = $repo->findPublic($this->project, $perPage, ($pageNr - 1) * $perPage);
        $postsResultSet = $this->postResultSetFactory->createFromEntities($posts);
        $postsResultSet->setAddAuthors($this->addAuthors);
        $postsResultSet->setAddCovers($this->addCovers);

        $settings = $this->project->getSettings();
        $galleryBaseUrl = $settings->get('gallery.base_url');
        if ($galleryBaseUrl) {
            $postsResultSet->setGalleryBaseUrl($galleryBaseUrl);
        }

        $this->assign('pagination', $pagination);
        $this->assign('post_gallery_base_url', $galleryBaseUrl);
        $this->assign('posts', $postsResultSet->toArray());
        $this->assignMonthsArchiveData(null, null);
        $this->assignTagsData();

        return null;
    }

    protected function loadPostTagIndex(string $tagName, int $pageNr, int $perPage): ?Response
    {
        $em = $this->entityManager;
        $tagRepo = $em->getRepository(Tag::class);
        $tag = $tagRepo->findOneBy(['urlId' => $tagName, 'isPublic' => true]);
        $privateTag = false;
        if (null === $tag) {
            $tag = $tagRepo->findOneBy(['privateSlug' => $tagName, 'isPublic' => false]);
            if (null === $tag) {
                return $this->renderProject404();
            }
            $privateTag = true;
        }
        $this->tag = $tag;

        /** @var PostRepository $repo */
        $repo = $em->getRepository(PostEntity::class);

        $search = new PostSearchRequest();
        $search->project = $this->project;
        $search->tag = $tag;
        if ($privateTag) {
            $search->includePrivate = true;
        }

        $posts = $repo->findByRequest($search, $perPage, ($pageNr - 1) * $perPage);
        $total = $repo->countByRequest($search);

        $postsResultSet = $this->postResultSetFactory->createFromEntities($posts);
        $postsResultSet->setAddAuthors($this->addAuthors);
        $postsResultSet->setAddCovers($this->addCovers);

        $settings = $this->project->getSettings();
        $galleryBaseUrl = $settings->get('gallery.base_url');
        if ($galleryBaseUrl) {
            $postsResultSet->setGalleryBaseUrl($galleryBaseUrl);
        }

        $pagination = new Pagination($total, $perPage);
        $pagination->setCurrentPage($pageNr);

        $this->assign('pagination', $pagination);
        $this->assign('post_gallery_base_url', $galleryBaseUrl);
        $this->assign('posts', $postsResultSet->toArray());
        $this->assign('tag', $tag);
        $this->assignMonthsArchiveData(null, null);
        $this->assignTagsData();

        return null;
    }

    protected function loadMonthlyArchive(int $year, int $month, int $pageNr, int $perPage): null
    {
        /** @var PostRepository $repo */
        $repo = $this->entityManager->getRepository(PostEntity::class);
        $posts = $repo->findPublicByMonth($this->project, $year, $month, $perPage, ($pageNr - 1) * $perPage);

        $postsResultSet = $this->postResultSetFactory->createFromEntities($posts);
        $postsResultSet->setAddAuthors($this->addAuthors);
        $postsResultSet->setAddCovers($this->addCovers);

        $settings = $this->project->getSettings();
        $galleryBaseUrl = $settings->get('gallery.base_url');
        if ($galleryBaseUrl) {
            $postsResultSet->setGalleryBaseUrl($galleryBaseUrl);
        }

        $pagination = new Pagination($repo->countPublicByMonth($this->project, $year, $month), $perPage);
        $pagination->setCurrentPage($pageNr);

        $this->assign('pagination', $pagination);
        $this->assign('post_gallery_base_url', $galleryBaseUrl);
        $this->assign('posts', $postsResultSet->toArray());
        $this->assignMonthsArchiveData($year, $month);
        $this->assignTagsData();

        return null;
    }

    protected function assignMonthsArchiveData(?int $year, ?int $month, int $lastMonths = 6): void
    {
        $lastMonthsData = Date::lastMonths($lastMonths, $this->getLocale());
        if ($month && $year) {
            foreach ($lastMonthsData as &$item) {
                if ($item['year'] === $year && $item['month'] === $month) {
                    $item['active'] = true;
                }
            }
            unset($item);
        }
        $this->assign('last_months', $lastMonthsData);
        $this->assign('month', $month);
        $this->assign('year', $year);
    }

    public function assignTagsData(): void
    {
        $repo = $this->entityManager->getRepository(Tag::class);
        $tags = $repo->findBy(
            [
                'project' => $this->project->entity,
            ],
            ['name' => 'ASC'],
            10
        );
        $this->assign('suggested_tags', $tags);
    }

    protected function loadPost(string $urlId, bool $includePrivate = false): ?Response
    {
        $post = $this->postFactory->loadByUrlId($urlId, $includePrivate);
        if (null === $post) {
            $post = $this->postFactory->loadByPrivateSlug($urlId);
            if (null === $post) {
                return $this->renderProject404();
            }
        }
        $this->post = $post;

        $postResultSet = $this->postResultSetFactory->createFromEntities([$post->entity]);
        $array = $postResultSet->toArray();
        $post = reset($array);

        $galleryManager = $this->post->getGalleryManager();
        $gallery = $galleryManager->loadGallery('post');
        $resultSet = $gallery->getItemsResultSet();

        $settings = $this->project->getSettings();
        $galleryBaseUrl = $settings->get('gallery.base_url');
        if ($galleryBaseUrl) {
            $resultSet->setBaseUrl($galleryBaseUrl);
        }

        /** @var PostRepository $repo */
        $repo = $this->entityManager->getRepository(PostEntity::class);
        $request = new PostSearchRequest();
        $request->project = $this->project;
        $request->maxId = $post['id'];
        $nextPosts = $repo->findByRequest($request, 3);
        $nextPostsResultSet = $this->postResultSetFactory->createFromEntities($nextPosts);

        $this->assign('post', $post);
        $this->assign('post_authors', $this->post->getAuthors());
        $this->assign('post_gallery', $resultSet->toArray());
        $this->assign('next_posts', $nextPostsResultSet->toArray());

        return null;
    }

    protected function getGallery(string $name): ?Gallery
    {
        if (!$this->post) {
            throw new LogicException('Post not loaded');
        }

        $post = $this->post;

        if (isset($this->galleries[$name])) {
            return $this->galleries[$name];
        }

        $gallery = $post->getGalleryManager()->getGallery($name);
        if (null === $gallery) {
            return null;
        }

        $this->galleries[$name] = $gallery;

        return $gallery;
    }
}
