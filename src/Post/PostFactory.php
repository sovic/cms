<?php

namespace Sovic\Cms\Post;

use Sovic\Cms\Entity\Post as PostEntity;
use Sovic\Cms\Project\ProjectEntityModelFactoryInterface;
use Sovic\Cms\Project\ProjectEntityModelFactoryTrait;
use Sovic\Cms\Repository\PostRepository;
use Sovic\Common\Model\EntityModelFactory;

final class PostFactory extends EntityModelFactory implements ProjectEntityModelFactoryInterface
{
    use ProjectEntityModelFactoryTrait;

    public function loadByEntity(?PostEntity $entity = null): ?Post
    {
        return $this->loadEntityModel($entity, Post::class);
    }

    public function loadById(int $id): ?Post
    {
        $criteria = $this->getProjectSelectCriteria();
        $criteria['id'] = $id;

        return $this->loadModelBy(PostEntity::class, Post::class, $criteria);
    }

    public function loadByUrlId(string $urlId, bool $allowNonPublished = false): ?Post
    {
        $criteria = $this->getProjectSelectCriteria();
        $urlId = trim($urlId, '/\\'); // trim leading / trailing slashes
        $criteria['urlId'] = $urlId;

        /** @var Post $model */
        $model = $this->loadModelBy(PostEntity::class, Post::class, $criteria);
        if (null === $model) {
            return null;
        }
        if (!$allowNonPublished && !$model->entity->isPublic()) {
            return null;
        }

        return $model;
    }

    public function loadByPrivateSlug(string $slug): ?Post
    {
        $criteria = $this->getProjectSelectCriteria();
        $criteria['public'] = false;
        $criteria['privateSlug'] = $slug;

        return $this->loadModelBy(PostEntity::class, Post::class, $criteria);
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
