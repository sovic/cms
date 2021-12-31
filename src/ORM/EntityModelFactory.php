<?php

namespace SovicCms\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

abstract class EntityModelFactory
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @param mixed $entity Entity
     * @param string $modelClass
     * @return mixed Model
     */
    protected function loadEntityModel(mixed $entity, string $modelClass): mixed
    {
        if (null === $entity) {
            return null;
        }

        $model = new $modelClass();
        $model->setEntityManager($this->entityManager);
        $model->setEntity($entity);

        return $model;
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

    /**
     * @param string $entityClass
     * @param string $modelClass
     * @param array $criteria
     * @return mixed
     */
    protected function loadModelBy(string $entityClass, string $modelClass, array $criteria): mixed
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository($entityClass);
        $entity = $repository->findOneBy($criteria);
        if (!$entity) {
            return null;
        }

        return $this->loadEntityModel($entity, $modelClass);
    }
}
