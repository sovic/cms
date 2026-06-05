<?php

namespace Sovic\Cms\Page;

use Doctrine\ORM\Query\Expr\Join;
use Sovic\Cms\Entity\PageTag;
use Sovic\Cms\Entity\Tag as TagEntity;
use Sovic\Common\Model\AbstractEntityModel;
use Sovic\Cms\Entity\GalleryModelInterface;
use Sovic\Cms\Model\Trait\GalleryModelTrait;

/**
 * @property \Sovic\Cms\Entity\Page $entity
 */
class Page extends AbstractEntityModel implements GalleryModelInterface
{
    use GalleryModelTrait;

    public function getId(): int
    {
        return $this->entity->getId();
    }

    public function getGalleryModelName(): string
    {
        return 'page';
    }

    public function getGalleryModelId(): string
    {
        return $this->getId();
    }

    public function getPublicUrl(string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . '/' . $this->entity->getUrlId();
    }

    public function getHeading(): array
    {
        return explode('/', $this->entity->getHeading());
    }

    public function getMetaTitle(): string
    {
        if ($this->entity->getMetaTitle()) {
            return $this->entity->getMetaTitle();
        }

        return $this->entity->getHeading();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $heading = array_map('trim', explode('/', $this->entity->getHeading()));
        $urlParts = array_map('trim', explode('/', $this->entity->getUrlId()));

        // auto-create breadcrumbs by heading parts (max. 2-parts)
        $i = 0;
        $breadcrumbUrl = '';
        foreach ($heading as $breadcrumb) {
            if (isset($urlParts[$i]) && $i < (count($heading) - 1)) {
                $breadcrumbUrl .= '/' . $urlParts[$i];
                $breadcrumbs[] = ['name' => $breadcrumb, 'url' => $breadcrumbUrl];
            } else {
                $breadcrumbs[] = ['name' => $breadcrumb, 'url' => null];
            }
            $i++;
            if ($i > 1) {
                break;
            }
        }

        return $breadcrumbs;
    }

    /**
     * @return TagEntity[]
     */
    public function getTags(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t');
        $qb->from(TagEntity::class, 't');
        $qb->leftJoin(PageTag::class, 'pt', Join::WITH, 't.id = pt.tagId');
        $qb->where('pt.pageId = :page_id');
        $qb->setParameter('page_id', $this->getId());
        $qb->addOrderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function addTag(TagEntity $tag): void
    {
        $em = $this->getEntityManager();
        $pageTag = $em->getRepository(PageTag::class)->findOneBy(
            ['pageId' => $this->getId(), 'tagId' => $tag->getId()]
        );
        if (!$pageTag) {
            $pageTag = new PageTag();
            $pageTag->setPageId($this->getId());
            $pageTag->setPage($this->entity);
            $pageTag->setTagId($tag->getId());
            $pageTag->setTag($tag);
            $em->persist($pageTag);
            $em->flush();
        }
    }

    public function removeTag(TagEntity $tag): void
    {
        $em = $this->getEntityManager();
        $pageTag = $em->getRepository(PageTag::class)->findOneBy(
            ['pageId' => $this->getId(), 'tagId' => $tag->getId()]
        );
        if ($pageTag) {
            $em->remove($pageTag);
            $em->flush();
        }
    }

    /**
     * @param string[] $tagNames
     */
    public function syncTagsByNames(array $tagNames): void
    {
        $em = $this->getEntityManager();

        $existingPageTags = $em->getRepository(PageTag::class)->findBy(['pageId' => $this->getId()]);
        $existingTagIds = array_map(static fn(PageTag $pt) => $pt->getTagId(), $existingPageTags);

        $newTagEntities = [];
        if (!empty($tagNames)) {
            $newTagEntities = $em->getRepository(TagEntity::class)->findBy(['name' => array_values($tagNames)]);
        }
        $newTagIds = array_map(static fn(TagEntity $t) => $t->getId(), $newTagEntities);

        foreach ($existingPageTags as $pageTag) {
            if (!in_array($pageTag->getTagId(), $newTagIds, true)) {
                $em->remove($pageTag);
            }
        }

        foreach ($newTagEntities as $tag) {
            if (!in_array($tag->getId(), $existingTagIds, true)) {
                $pageTag = new PageTag();
                $pageTag->setPageId($this->getId());
                $pageTag->setPage($this->entity);
                $pageTag->setTagId($tag->getId());
                $pageTag->setTag($tag);
                $em->persist($pageTag);
            }
        }

        $em->flush();
    }

    /**
     * Double-check what you add to this method, this will be loaded on every page!
     */
    public function toArray(): array
    {
        if (null === $this->entity) {
            return [];
        }

        $entity = $this->entity;
        $galleryManager = $this->getGalleryManager();
        $gallery = $galleryManager->loadGallery('page');
        $heroImage = $gallery->getHeroImage();

        return [
            // meta
            'meta_title' => $this->getMetaTitle(),
            'meta_description' => $entity->getMetaDescription(),
            'meta_keywords' => $entity->getMetaKeywords(),

            //
            'gallery' => $gallery,
            'hero_image' => $heroImage,
            'menu_active' => '/' . $entity->getUrlId(),
            'show_toc', $entity->hasToc(),
        ];
    }
}
