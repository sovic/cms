<?php

namespace Sovic\Cms\Enum;

use SortDirection;

interface SortInterface
{
    /**
     * Doctrine order by definition, applied in given order.
     *
     * @return array<string, SortDirection> field (without alias) => direction
     */
    public function getOrderBy(): array;
}
