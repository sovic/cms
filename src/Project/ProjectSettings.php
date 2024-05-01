<?php

namespace Sovic\Cms\Project;

use Symfony\Component\HttpFoundation\ParameterBag;

class ProjectSettings extends ParameterBag
{
    public array $templateKeys = [];

    public function __construct(array $parameters = [], array $templateParameters = [])
    {
        parent::__construct($parameters);
        $this->templateKeys = $templateParameters;
    }

    public function getTemplateData(): array
    {
        $settings = [];
        foreach ($this->templateKeys as $key => $value) {
            $templateKey = str_replace(['.', ' '], '_', $key);
            $settings[$templateKey] = $this->get($key);
        }

        return $settings;
    }
}
