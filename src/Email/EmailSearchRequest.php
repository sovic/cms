<?php

namespace Sovic\Cms\Email;

use Sovic\Common\DataList\AbstractSearchRequest;

class EmailSearchRequest extends AbstractSearchRequest
{
    public function toArray(): array
    {
        return [
            'limit' => $this->getLimit(),
            'page' => $this->getPage(),
            'search' => $this->getSearch() ?? '',
        ];
    }
}