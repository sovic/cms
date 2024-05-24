<?php

namespace Sovic\Cms\Controller;

use Sovic\Cms\Project\Project;
use Sovic\Cms\Project\ProjectFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

trait ProjectControllerTrait
{
    private Project $project;
    private ?Environment $projectTwigEnvironment = null;

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function initializeProjectController(
        ProjectFactory $projectFactory,
        Request        $request,
        ?Environment   $twig = null,
    ): void {
        $this->setProject($projectFactory->loadByRequest($request));
        $this->assignProjectData();
        $this->projectTwigEnvironment = $twig;
        $this->setLocale($request->getLocale());
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
        $twig = $this->projectTwigEnvironment;

        return $twig->getLoader()->exists($projectTemplatePath) ? $projectTemplatePath : null;
    }

    protected function renderProject404(string $template = 'page/404', array $parameters = []): Response
    {
        $template = $this->getProjectTemplatePath($template);

        return parent::render404($template, $parameters);
    }
}
