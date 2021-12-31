<?php

namespace SovicCms\Post;

use SovicCms\Entity\Post as PostEntity;
use SovicCms\ORM\EntityModelFactory;
use SovicCms\Repository\PostRepository;

final class PostFactory extends EntityModelFactory
{
    public function loadByEntity(?PostEntity $entity = null): ?Post
    {
        return $this->loadEntityModel($entity, Post::class);
    }

    public function loadById(int $id): ?Post
    {
        return $this->loadModelById(PostEntity::class, Post::class, $id);
    }

    public function loadPublicPostById(int $id): ?Post
    {
        return $this->loadModelBy(
            PostEntity::class,
            Post::class,
            ['id' => $id, 'public' => 1]
        );
    }

    /**
     * @return Post[]
     */
    public function getPosts(): array
    {
        /** @var PostRepository $repository */
        // $repository = self::$doctrine->getRepository(Post::class);
        /** @var Post $entity */
        // $entity = $repository->findOneBy(['id' => $id, 'public' => 1]);

        return [];
    }
}
