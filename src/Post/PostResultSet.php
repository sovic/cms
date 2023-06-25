<?php

namespace Sovic\Cms\Post;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Gallery\Entity\GalleryItem;
use Sovic\Gallery\Gallery\GalleryHelper;

class PostResultSet
{
    private EntityManagerInterface $entityManager;
    /** @var Post[] */
    private array $posts = [];
    /** @var Post[] */
    private array $postsList;
    /** @var array */
    private array $authorsIds = [];

    private bool $addAuthors = false;
    private bool $addCovers = false;

    private string $galleryBaseUrl = '';

    /**
     * PostResultSet constructor.
     * @param EntityManagerInterface $entityManager
     * @param Post[] $posts
     */
    public function __construct(EntityManagerInterface $entityManager, array $posts)
    {
        $this->entityManager = $entityManager;
        foreach ($posts as $post) {
            $this->posts[] = $post;
        }

        $postsList = [];
        foreach ($this->getPosts() as $post) {
            $postsList[$post->getId()] = $post;
        }
        $this->postsList = $postsList;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function setAuthorsIds(array $authorsIds): void
    {
        $this->authorsIds = $authorsIds;
    }

    public function setAddAuthors(bool $addAuthors): void
    {
        $this->addAuthors = $addAuthors;
    }

    public function setAddCovers(bool $addCovers): void
    {
        $this->addCovers = $addCovers;
    }

    public function setGalleryBaseUrl(string $galleryBaseUrl): void
    {
        $this->galleryBaseUrl = $galleryBaseUrl;
    }

    /**
     * @return Post[]
     */
    public function getPosts(): array
    {
        return $this->posts;
    }

    /**
     * @return int[]
     */
    public function getPostsIdList(): array
    {
        return array_keys($this->postsList);
    }

    private function loadCovers(): array
    {
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $entities = $repo->findGalleriesCovers('post', $this->getPostsIdList());
        $result = [];
        /** @var GalleryItem $entity */
        foreach ($entities as $entity) {
            $cover = GalleryHelper::getMediaPaths(
                $entity,
                $this->galleryBaseUrl,
                GalleryHelper::SIZES_SET_ALL
            );
            $result[$entity->getModelId()] = $cover;
        }

        return $result;
    }

    public function toArray(): array
    {
        // covers
        $covers = $this->addCovers ? $this->loadCovers() : [];

        // url slugify
        $slugify = new Slugify();
        $slugify->activateRuleSet('default');

        $results = [];
        $groupByAuthors = !empty($this->authorsIds);
        foreach ($this->getPosts() as $post) {
            $id = $post->getId();
            $entity = $post->getEntity();

            $item = [
                'content' => $entity->getContent(),
                'heading' => $post->getHeading(),
                'id' => $id,
                'intro_text' => $post->getIntroText(),
                'is_featured' => $entity->isFeatured(),
                'is_gallery_enabled' => $entity->isGalleryEnabled(),
                'keywords' => $entity->getMetaKeywords(),
                'perex' => $entity->getPerex(),
                'publish_date' => $entity->getPublishDate(),
                'tags' => [],
                'cover_photo' => $covers[$id] ?? null,
                'url' => '/post/' . $post->getId() . '-' . $slugify->slugify($entity->getName()),
                'url_id' => $entity->getUrlId(),
            ];
            if ($this->addAuthors) {
                $item['authors'] = $post->getAuthors();
            }

            if ($groupByAuthors) {
                $authorIds = $this->authorsIds[$post->getId()];
                foreach ($authorIds as $authorId) {
                    if (!isset($results[$authorId])) {
                        $results[$authorId] = [];
                    }
                    $results[$authorId][$id] = $item;
                }
            } else {
                $results[$id] = $item;
            }
        }

        return $results;
    }
}
