<?php

namespace SovicCms\Post;

use SovicCms\Gallery\GalleryManager;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;

class PostResultSet
{
    private EntityManagerInterface $entityManager;
    /** @var Post[] */
    private array $posts = [];
    /** @var Post[] */
    private array $postsList;

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
        $entityManager = $this->getEntityManager();
        // title photos
        $galleryManager = new GalleryManager($entityManager, 'post', $this->getPostsIdList());
        $titlePhotos = $galleryManager->getTitlePhotos('post');
        // url slugify
        $slugify = new Slugify();
        $slugify->activateRuleSet('default');

        $results = [];
        foreach ($this->getPosts() as $post) {
            $id = $post->getId();
            $entity = $post->getEntity();
            $item = [
                'content' => $entity->getContent(),
                'heading' => $post->getHeading(),
                'id' => $id,
                'is_featured' => $entity->isFeatured(),
                'keywords' => $entity->getMetaKeywords(),
                'perex' => $post->getPerex(),
                'publish_date' => $entity->getPublishDate(),
                'tags' => [],
                'title' => $entity->getHeading(), // TODO remove
                'title_image' => $titlePhotos[$id] ?? null,
                'title_image_url' => isset($titlePhotos[$id]) ? $titlePhotos[$id]['full'] : null,
                'url' => '/post/' . $post->getId() . '-' . $slugify->slugify($entity->getName()),
                'url_id' => $entity->getUrlId(),
            ];
            $results[$id] = $item;
        }

        return $results;
    }
}
