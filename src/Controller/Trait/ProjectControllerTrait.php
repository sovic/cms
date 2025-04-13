<?php

namespace Sovic\Cms\Controller\Trait;

use Sovic\Common\Controller\Trait\BaseControllerTrait;
use Sovic\Common\Project\Project;
use Sovic\Common\Project\Settings;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

trait ProjectControllerTrait
{
    use BaseControllerTrait;

    protected Project $project;
    protected Settings $settings;
    protected ?Environment $projectTwig = null;

    #[Required]
    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function setProjectTwig(?Environment $projectTwig): void
    {
        $this->projectTwig = $projectTwig;
    }

    #[Required]
    public function setSettings(Settings $settings): void
    {
        $this->settings = $settings;
    }

    public function assignProjectData(): void
    {
        $project = $this->project;

        $this->assign('project', $project->getSlug());
        $this->assignArray($this->settings->getTemplateData());
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

    /** @noinspection PhpMultipleClassDeclarationsInspection */
    protected function renderProject404(string $template = 'page/404', array $parameters = []): Response
    {
        $template = $this->getProjectTemplatePath($template);

        return $this->render404($template, $parameters);
    }
}
