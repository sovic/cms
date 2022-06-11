<?php

namespace SovicCms\Post;

use Doctrine\ORM\EntityManagerInterface;

class PostResultSetFactory
{
    public function __construct(private EntityManagerInterface $entityManager, private PostFactory $postFactory)
    {
    }

    /**
     * @param \SovicCms\Entity\Post[] $posts
     * @return PostResultSet
     */
    public function createFromEntities(array $posts): PostResultSet
    {
        $results = [];
        foreach ($posts as $entity) {
            /** @var Post $post */
            $post = $this->postFactory->loadByEntity($entity);
            if (null === $post) {
                continue;
            }
            $results[] = $post;
        }

        return new PostResultSet($this->entityManager, $results);
    }
}
