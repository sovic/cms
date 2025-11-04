<?php

namespace Sovic\Cms\Email;

interface EmailListInterface
{
    /** @return EmailIdInterface[] */
    public function getEmailIds(): array;

    /** @return string[] */
    public function getDomainEmails(): array;
}
