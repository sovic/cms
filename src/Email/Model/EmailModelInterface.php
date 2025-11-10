<?php

namespace Sovic\Cms\Email\Model;

use Sovic\Cms\Email\EmailIdInterface;

interface EmailModelInterface
{
    public function getId(): EmailIdInterface;

    public function getData(): array;
}
