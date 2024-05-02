<?php

namespace Sovic\Cms\ORM;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Sovic\Gallery\Entity\GalleryModelInterface;
use Sovic\Gallery\Gallery\GalleryManager;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractEntityModel
{
    protected EntityManagerInterface $entityManager;
    protected RouterInterface $router;
    protected TranslatorInterface $translator;

    public mixed $entity;

    protected GalleryManager $galleryManager;

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    #[Required]
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    #[Required]
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function flush(): void
    {
        $this->entityManager->persist($this->entity);
        $this->entityManager->flush();
    }

    public function remove(): void
    {
        $this->entityManager->remove($this->entity);
        $this->entityManager->flush();
    }

    public function refresh(): void
    {
        $this->entityManager->refresh($this->entity);
    }

    public function getGalleryManager(): GalleryManager
    {
        if (!$this instanceof GalleryModelInterface) {
            throw new RuntimeException('Not yet implemented');
        }
        if (empty($this->galleryManager)) {
            $manager = new GalleryManager($this->getGalleryModelName(), $this->getGalleryModelId());
            $manager->setEntityManager($this->entityManager);

            $this->galleryManager = $manager;
        }

        return $this->galleryManager;
    }
}
