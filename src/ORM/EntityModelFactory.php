<?php

namespace SovicCms\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class EntityModelFactory
{
    protected EntityManagerInterface $entityManager;
    protected RouterInterface $router;
    protected TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface        $router,
        TranslatorInterface    $translator
    ) {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->translator = $translator;
    }

    protected function loadEntityModel(mixed $entity, string $modelClass): mixed
    {
        if (null === $entity) {
            return null;
        }

        $model = new $modelClass();
        $model->setEntityManager($this->entityManager);
        $model->setTranslator($this->translator);
        $model->setRouter($this->router);
        $model->setEntity($entity);

        return $model;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @param string $entityClass
     * @param string $modelClass
     * @param int $id
     * @return mixed
     */
    protected function loadModelById(string $entityClass, string $modelClass, int $id): mixed
    {
        return $this->loadModelBy($entityClass, $modelClass, ['id' => $id]);
    }

    protected function loadModelBy(
        string $entityClass,
        string $modelClass,
        array  $criteria,
        ?array $orderBy = null
    ): mixed {
        $repository = $this->entityManager->getRepository($entityClass);
        $entity = $repository->findOneBy($criteria, $orderBy);
        if (!$entity) {
            return null;
        }

        return $this->loadEntityModel($entity, $modelClass);
    }
}
