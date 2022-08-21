<?php

namespace SovicCms\Post;

use Doctrine\ORM\EntityManagerInterface;
use SovicCms\Entity\Author;
use SovicCms\Entity\Post as PostEntity;
use SovicCms\Entity\PostAuthor;

class PostResultSetFactory
{
    public function __construct(private EntityManagerInterface $entityManager, private PostFactory $postFactory)
    {
    }

    /**
     * @param PostEntity[] $posts
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

    /**
     * TODO limit per author, total limit ...
     *
     * @param Author[] $authors
     * @param int|null $limitPerAuthor
     * @return PostResultSet
     */
    public function loadByAuthors(array $authors, ?int $limitPerAuthor = null): PostResultSet
    {
        $authorsIds = [];
        foreach ($authors as $author) {
            $authorsIds[] = $author->getId();
        }
        /** @var PostAuthor[] $postsAuthors */
        $postsAuthors = $this->entityManager
            ->getRepository(PostAuthor::class)
            ->findBy(['authorId' => $authorsIds]);
        $postIds = [];
        $postIdAuthorId = [];
        foreach ($postsAuthors as $postsAuthor) {
            $postId = $postsAuthor->getPostId();
            $postIds[] = $postId;
            if (!isset($postIdAuthorId[$postId])) {
                $postIdAuthorId[$postId] = [];
            }
            $postIdAuthorId[$postId][] = $postsAuthor->getAuthorId();
        }
        $posts = $this->entityManager
            ->getRepository(PostEntity::class)
            ->findBy(['id' => $postIds, 'public' => 1]);

        $prs = $this->createFromEntities($posts);
        $prs->setAuthorsIds($postIdAuthorId);

        return $prs;
    }
}
