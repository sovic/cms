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
        $galleryManager = new GalleryManager($entityManager, 'post');
        $titlePhotos = $galleryManager->getTitlePhotos($this->getPostsIdList(), 'post');
        // url slugify
        $slugify = new Slugify();
        $slugify->activateRuleSet('default');

        $results = [];
        foreach ($this->getPosts() as $post) {
            $id = $post->getId();
            $item = [
                'id' => $id,
                'published' => $post->getEntity()->getPublished(),
                'title' => $post->getEntity()->getHeading(),
                'image' => null,
                'url' => '/post/' . $post->getId() . '-' . $slugify->slugify($post->getEntity()->getName()),
                'tags' => [],
            ];
            if (isset($titlePhotos[$id])) {
                $item['image'] = $titlePhotos[$id];
            }
            $results[$id] = $item;
        }

        return $results;
    }
}
