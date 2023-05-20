<?php

namespace SovicCms\Post;

use Doctrine\ORM\Query\Expr\Join;
use Sovic\Gallery\Entity\GalleryModelInterface;
use Sovic\Gallery\Gallery\Gallery;
use SovicCms\Entity\Author;
use SovicCms\Entity\PostAuthor;
use SovicCms\ORM\AbstractEntityModel;

/**
 * @method \SovicCms\Entity\Post getEntity()
 */
class Post extends AbstractEntityModel implements GalleryModelInterface
{
    public function getId(): int
    {
        return $this->getEntity()->getId();
    }

    public function getHeading(): string
    {
        return $this->getEntity()->getHeading() ?: $this->getEntity()->getName();
    }

    public function getIntroText(int $length = 250): ?string
    {
        $perex = $this->getEntity()->getPerex();
        if ($perex) {
            return $perex;
        }
        $content = strip_tags($this->getEntity()->getContent());

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

    public function getCoverImage(): ?array
    {
        return $this->getGallery()->getCoverImage();
    }

    public function getGallery(): Gallery
    {
        return $this->getGalleryManager()->loadGallery('post');
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
}
