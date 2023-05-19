<?php

namespace SovicCms\Post;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;

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

    public function toArray(): array
    {
        // $entityManager = $this->getEntityManager();
        // title photos
        // $galleryManager = new GalleryManager('post', $this->getPostsIdList());
        // $coverPhotos = $galleryManager->getCoverPhotos('post');
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
                'keywords' => $entity->getMetaKeywords(),
                'perex' => $entity->getPerex(),
                'publish_date' => $entity->getPublishDate(),
                'tags' => [],
                'title' => $entity->getHeading(), // TODO remove
                'cover_photo' => null, // $coverPhotos[$id] ?? null,
                'cover_photo_url' => null, // isset($coverPhotos[$id]) ? $coverPhotos[$id]['full'] : null,
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
