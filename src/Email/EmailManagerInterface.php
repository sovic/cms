<?php

namespace Sovic\Cms\Email;

use Sovic\Cms\Email\Model\EmailModelInterface;

interface EmailManagerInterface
{
    public function send(
        EmailModelInterface $model,
        string              $emailTo,
        ?string             $sender = null,
        ?string             $replyTo = null,
        ?string             $template = null,
        ?bool               $log = false,
    ): bool;
}
