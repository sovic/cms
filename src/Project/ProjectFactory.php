<?php

namespace Sovic\Cms\Project;

use Sovic\Cms\ORM\EntityModelFactory;
use Sovic\Cms\Entity\Project as ProjectEntity;
use Symfony\Component\HttpFoundation\Request;

final class ProjectFactory extends EntityModelFactory
{
    public function loadById(int $id): ?Project
    {
        return $this->loadModelBy(ProjectEntity::class, Project::class, ['id' => $id]);
    }

    public function loadBySlug(string $slug): ?Project
    {
        return $this->loadModelBy(ProjectEntity::class, Project::class, ['slug' => $slug]);
    }

    public function loadByRequest(?Request $request = null): ?Project
    {
        if (!$request) {
            $envProject = $_SERVER['PROJECT'] ?? null;
            if ($envProject) {
                return $this->loadModelBy(ProjectEntity::class, Project::class, ['slug' => $envProject]);
            }

            return null;
        }

        $envProject = $request->server->get('PROJECT');
        if ($envProject) {
            return $this->loadModelBy(ProjectEntity::class, Project::class, ['slug' => $envProject]);
        }

        $host = $request->getHost();
        $em = $this->getEntityManager();
        $result = $em->getRepository(ProjectEntity::class)
            ->createQueryBuilder('p')
            ->where('p.domains LIKE :host')
            ->setParameter('host', '%' . $host . '%')
            ->getQuery()
            ->getResult();
        if ($result) {
            return $this->loadEntityModel($result[0], Project::class);
        }

        return null;
    }
}
