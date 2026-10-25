<?php

namespace Sovic\Cms\Post;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Sovic\Cms\Entity\GalleryItem;
use Sovic\Cms\Entity\PostTag;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Gallery\GalleryHelper;
use Sovic\Cms\Repository\GalleryItemRepository;

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
    private int $limitPerAuthor = 5;
    private bool $addCovers = false;

    private string $galleryBaseUrl = '';

    /**
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

    public function setLimitPerAuthor(int $limitPerAuthor): void
    {
        $this->limitPerAuthor = $limitPerAuthor;
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
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $entities = $repo->findGalleriesCovers('post', $this->getPostsIdList(), 'post');
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

    /**
     * @return array<int, string[]> public tag names by post id
     */
    private function loadTags(): array
    {
        $postIds = $this->getPostsIdList();
        if (empty($postIds)) {
            return [];
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('pt.postId AS post_id', 't.name AS name');
        $qb->from(PostTag::class, 'pt');
        $qb->innerJoin(Tag::class, 't', Join::WITH, 't.id = pt.tagId');
        $qb->where('pt.postId IN (:post_ids)');
        $qb->andWhere('t.isPublic = :is_public');
        $qb->setParameter('post_ids', $postIds);
        $qb->setParameter('is_public', true);
        $qb->addOrderBy('t.name', 'ASC');

        $result = [];
        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $result[(int) $row['post_id']][] = $row['name'];
        }

        return $result;
    }

    public function toArray(): array
    {
        // covers
        $covers = $this->addCovers ? $this->loadCovers() : [];

        // tags
        $tags = $this->loadTags();

        $results = [];
        $groupByAuthors = !empty($this->authorsIds);
        foreach ($this->getPosts() as $post) {
            $id = $post->getId();
            $entity = $post->entity;

            $item = [
                'authors' => null,
                'content' => $entity->getContent(),
                'cover_photo' => $covers[$id] ?? null,
                'heading' => $post->getHeading(),
                'id' => $id,
                'intro_text' => $post->getIntroText(),
                'is_featured' => $entity->isFeatured(),
                'is_gallery_enabled' => $entity->isGalleryEnabled(),
                'is_published' => $entity->isPublished(),
                'meta_description' => $entity->getMetaDescription(),
                'meta_keywords' => $entity->getMetaKeywords(),
                'meta_title' => $entity->getMetaTitle() ?: $post->getHeading(),
                'perex' => $entity->getPerex(),
                'private_slug' => $entity->getPrivateSlug(),
                'publish_date' => $entity->getPublishDate(),
                'subtitle' => $entity->getSubtitle(),
                'tags' => $tags[$id] ?? [],
                'url' => $post->getUrl(),
                'url_id' => $post->getUrlId(),
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
                    if (count($results[$authorId]) < $this->limitPerAuthor) {
                        $results[$authorId][$id] = $item;
                    }
                }
            } else {
                $results[$id] = $item;
            }
        }

        return $results;
    }
}
