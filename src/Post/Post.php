<?php

namespace Sovic\Cms\Post;

use Cocur\Slugify\Slugify;
use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Join;
use Sovic\Cms\Entity\PostTag;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Model\Trait\PrivateSlugTrait;
use Sovic\Common\Model\AbstractEntityModel;
use Sovic\Cms\Entity\GalleryModelInterface;
use Sovic\Cms\Entity\Author;
use Sovic\Cms\Entity\PostAuthor;
use Sovic\Cms\Model\Trait\GalleryModelTrait;

/**
 * @property \Sovic\Cms\Entity\Post $entity
 */
class Post extends AbstractEntityModel implements GalleryModelInterface
{
    use GalleryModelTrait;
    use PrivateSlugTrait;

    public function getId(): int
    {
        return $this->entity->getId();
    }

    public function getHeading(): string
    {
        return $this->entity->getHeading();
    }

    public function getIntroText(int $length = 250): ?string
    {
        $perex = $this->entity->getPerex();
        if ($perex) {
            return $perex;
        }
        $content = $this->entity->getContent();
        if (empty($content)) {
            return null;
        }
        $content = strip_tags($content);

        return $content ? substr($content, 0, $length) . '…' : null;
    }

    public function getGalleryModelName(): string
    {
        return 'post';
    }

    public function getGalleryModelId(): string
    {
        return $this->getId();
    }

    public function getAuthors(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('author');
        $qb->from(Author::class, 'author');
        $qb->leftJoin(PostAuthor::class, 'pa', Join::WITH, 'author.id = pa.authorId');
        $qb->where("pa.postId = :post_id");
        $qb->setParameter('post_id', $this->getId());
        $qb->addOrderBy('author.surname', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function save(bool $publish = false): void
    {
        $entity = $this->entity;

        // if urlId missing
        if (!isset($entity->urlId)) {
            $slugify = new Slugify();
            $slugify->activateRuleSet('default');

            $urlId = $slugify->slugify($entity->getHeading());
            $entity->setUrlId($urlId);
        }

        // if new entity
        if (!isset($entity->id)) {
            $entity->setCreated(new DateTimeImmutable());
            $entity->setCreateDate(new DateTimeImmutable());
        }

        // publish
        if ($publish) {
            $entity->setPublic(true);
            if ($entity->getPublishDate() === null) {
                $entity->setPublishDate(new DateTimeImmutable());
            }
        }

        $this->flush();
    }

    public function delete(): void
    {
        $galleries = $this->getGalleries();
        foreach ($galleries as $gallery) {
            $gallery->delete();
        }

        $this->getEntityManager()->remove($this->entity);
        $this->getEntityManager()->flush();
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t');
        $qb->from(Tag::class, 't');
        $qb->leftJoin(PostTag::class, 'pt', Join::WITH, 't.id = pt.tagId');
        $qb->where('pt.postId = :post_id');
        $qb->setParameter('post_id', $this->getId());
        $qb->addOrderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string[] $tagNames
     */
    public function syncTagsByNames(array $tagNames): void
    {
        $em = $this->getEntityManager();

        $existingPostTags = $em->getRepository(PostTag::class)->findBy(['postId' => $this->getId()]);
        $existingTagIds = array_map(static fn(PostTag $pt) => $pt->getTagId(), $existingPostTags);

        $newTagEntities = [];
        if (!empty($tagNames)) {
            $newTagEntities = $em->getRepository(Tag::class)->findBy(['name' => array_values($tagNames)]);
        }
        $newTagIds = array_map(static fn(Tag $t) => $t->getId(), $newTagEntities);

        foreach ($existingPostTags as $postTag) {
            if (!in_array($postTag->getTagId(), $newTagIds, true)) {
                $em->remove($postTag);
            }
        }

        foreach ($newTagEntities as $tag) {
            if (!in_array($tag->getId(), $existingTagIds, true)) {
                $postTag = new PostTag();
                $postTag->setPostId($this->getId());
                $postTag->setPost($this->entity);
                $postTag->setTagId($tag->getId());
                $postTag->setTag($tag);
                $em->persist($postTag);
            }
        }

        $em->flush();
    }

    public function addTag(Tag $tag): void
    {
        $postTag = $this->getEntityManager()
            ->getRepository(PostTag::class)
            ->findOneBy(
                [
                    'postId' => $this->getId(),
                    'tagId' => $tag->getId(),
                ]
            );
        if (!$postTag) {
            $postTag = new PostTag();
            $postTag->setPostId($this->getId());
            $postTag->setPost($this->entity);
            $postTag->setTagId($tag->getId());
            $postTag->setTag($tag);
            $this->getEntityManager()->persist($postTag);
            $this->getEntityManager()->flush();
        }
    }

    public function removeTag(Tag $tag): void
    {
        $postTag = $this->getEntityManager()
            ->getRepository(PostTag::class)
            ->findOneBy(
                [
                    'postId' => $this->getId(),
                    'tagId' => $tag->getId(),
                ]
            );
        if ($postTag) {
            $this->getEntityManager()->remove($postTag);
            $this->getEntityManager()->flush();
        }
    }
}
