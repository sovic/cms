<?php

namespace Sovic\Cms\Controller\Trait;

use Sovic\Cms\Project\Project;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

trait ProjectControllerTrait
{
    private Project $project;
    private ?Environment $projectTwig = null;

    #[Required]
    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function setProjectTwig(?Environment $projectTwig): void
    {
        $this->projectTwig = $projectTwig;
    }

    public function assignProjectData(): void
    {
        $project = $this->project;
        $settings = $project->getSettings();

        $this->assign('project', $project->getSlug());
        $this->assignArray($settings->getTemplateData());
    }

    public function getProjectTemplatePath(string $templatePath): string
    {
        $templatePath = str_ends_with($templatePath, '.html.twig') ? $templatePath : $templatePath . '.html.twig';

        return $this->tryProjectTemplate($templatePath) ?? $templatePath;
    }

    public function tryProjectTemplate(string $templatePath): ?string
    {
        $templatePath = str_ends_with($templatePath, '.html.twig') ? $templatePath : $templatePath . '.html.twig';
        $projectTemplatePath = 'project/' . $this->project->getSlug() . '/' . $templatePath;

        return $this->projectTwig?->getLoader()->exists($projectTemplatePath) ? $projectTemplatePath : null;
    }

    protected function renderProject404(string $template = 'page/404', array $parameters = []): Response
    {
        $template = $this->getProjectTemplatePath($template);

        return parent::render404($template, $parameters);
    }
}
