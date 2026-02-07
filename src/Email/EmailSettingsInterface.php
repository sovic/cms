<?php

namespace Sovic\Cms\Email;

interface EmailSettingsInterface
{
    /** @return EmailIdInterface[] */
    public function getEmailIds(): array;

    /** @return string[] */
    public function getDomainEmails(): array;

    public function getVariablesForEmailId(EmailIdInterface|string $emailId): array;
}
