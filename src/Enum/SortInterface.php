<?php

namespace Sovic\Cms\Enum;

interface SortInterface
{
    /**
     * Doctrine order by definition, applied in given order.
     *
     * @return array<string, 'ASC'|'DESC'> field (without alias) => direction
     */
    public function getOrderBy(): array;
}
