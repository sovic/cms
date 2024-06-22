<?php

namespace Sovic\Cms\Post;

use Cocur\Slugify\Slugify;
use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Join;
use Sovic\Cms\Entity\PostTag;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Model\Trait\GalleryModelTrait;
use Sovic\Common\Model\AbstractEntityModel;
use Sovic\Gallery\Entity\GalleryModelInterface;
use Sovic\Cms\Entity\Author;
use Sovic\Cms\Entity\PostAuthor;

/**
 * @property \Sovic\Cms\Entity\Post $entity
 */
class Post extends AbstractEntityModel implements GalleryModelInterface
{
    use GalleryModelTrait;

    public function getId(): int
    {
        return $this->entity->getId();
    }

    public function getHeading(): string
    {
        return $this->entity->getHeading() ?: $this->entity->getName();
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

    public function save(bool $publish = true): void
    {
        $entity = $this->entity;

        // if urlId missing
        if (!isset($entity->urlId)) {
            $slugify = new Slugify();
            $slugify->activateRuleSet('default');

            $urlId = $slugify->slugify($entity->getName());
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
            $postTag->setTagId($tag->getId());
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
