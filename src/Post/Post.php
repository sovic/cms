<?php

namespace SovicCms\Post;

use Doctrine\ORM\Query\Expr\Join;
use SovicCms\Entity\Author;
use SovicCms\Entity\PostAuthor;
use SovicCms\ORM\AbstractEntityModel;
use SovicCms\ORM\EntityModelGalleryInterface;

/**
 * @method \SovicCms\Entity\Post getEntity()
 */
class Post extends AbstractEntityModel implements EntityModelGalleryInterface
{
    public function getId(): int
    {
        return $this->getEntity()->getId();
    }

    public function getHeading(): string
    {
        return $this->getEntity()->getHeading() ?: $this->getEntity()->getName();
    }

    public function getIntroText(): ?string
    {
        $perex = $this->getEntity()->getPerex();
        if ($perex) {
            return $perex;
        }
        $content = strip_tags($this->getEntity()->getContent());

        return $content ? substr($content, 0, 250) . '…' : null;
    }

    public function getGalleryModelName(): string
    {
        return 'post';
    }

    public function getGalleryModelId(): string
    {
        return $this->getId();
    }

    public function getTitlePhoto(): ?array
    {
        return $this->getGalleryManager()->getTitlePhoto('post');
    }

    public function getGallery(): array
    {
        return $this->getGalleryManager()->getGallery('post');
    }

    public function getAuthors(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('author');
        $qb->from(Author::class, 'author');
        $qb->leftJoin(PostAuthor::class, 'pa', Join::WITH, 'author.id = pa.authorId');
        $qb->where("pa.postId = :post_id");
        $qb->setParameter('post_id', $this->getId());

        return $qb->getQuery()->getResult();
    }
}
