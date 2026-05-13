<?php

namespace Sovic\Cms\Enum;

enum MailerSettingKey: string implements SettingKeyInterface
{
    case DefaultContactEmail = 'mailer_default_contact_email';
    case PrimaryColor = 'mailer_primary_color';
    case SecondaryColor = 'mailer_secondary_color';

    public function getFormField(): string
    {
        return substr($this->value, 7); // strip 'mailer_'
    }

    public function getSettingKey(): string
    {
        return $this->value;
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::DefaultContactEmail => 'Výchozí kontaktní e-mail',
            self::PrimaryColor => 'Primární barva',
            self::SecondaryColor => 'Sekundární barva',
        };
    }
}
