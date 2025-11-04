<?php

namespace Sovic\Cms\Email;

interface EmailIdInterface
{
    public function getId(): string;

    public function getLabel(): string;

    /** @return string[] */
    public function getVariables(): array;
}
