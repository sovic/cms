<?php

namespace Sovic\Cms\Post;

use Sovic\Cms\Entity\Post as PostEntity;
use Sovic\Cms\ORM\EntityModelFactory;
use Sovic\Cms\Repository\PostRepository;

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

    public function loadByUrlId(string $urlId, bool $allowNonPublished = false): ?Post
    {
        $urlId = trim($urlId, '/\\'); // trim leading / trailing slashes
        /** @var Post $model */
        $model = $this->loadModelBy(PostEntity::class, Post::class, ['urlId' => $urlId]);
        if (null === $model) {
            return null;
        }
        if (!$allowNonPublished && !$model->entity->isPublic()) {
            return null;
        }

        return $model;
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
