<?php

namespace Sovic\Cms\Entity;

enum ChangelogActionId: int
{
    case Create = 1;
    case Remove = 2;
    case Update = 3;
    case Delete = 4;
    case Report = 5;

    public function trans(): string
    {
        return match ($this) {
            self::Create => 'Vytvoření',
            self::Remove => 'Odebrání',
            self::Update => 'Změna',
            self::Delete => 'Smazání',
            self::Report => 'Generování výstupu',
        };
    }
}
