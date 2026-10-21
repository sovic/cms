<?php

namespace Sovic\Cms\Post;

use Sovic\Cms\Enum\SortInterface;

enum PostSort: string implements SortInterface
{
    // newest published first
    case PublishDate = 'publish_date';
    // latest created first
    case Latest = 'latest';

    public function getOrderBy(): array
    {
        return match ($this) {
            self::PublishDate => [
                'publishDate' => 'DESC',
                'id' => 'DESC',
            ],
            self::Latest => [
                'id' => 'DESC',
            ],
        };
    }
}
