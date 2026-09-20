<?php

namespace Sovic\Cms\Ai\Dto;

final readonly class ComponentLibrary
{
    /**
     * @param array<string, string> $components component name => HTML snippet
     */
    public function __construct(
        public string $styleguide,
        public array  $components,
    ) {
    }
}
