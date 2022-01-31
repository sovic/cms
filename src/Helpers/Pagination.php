<?php

namespace SovicCms\Helpers;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

class Pagination
{
    private int $total;
    private int $perPage;
    private int $currentPage = 1;

    public function __construct(int $total, int $perPage)
    {
        $this->total = $total;
        $this->perPage = $perPage;
    }

    #[Pure]
    public function getPageCount(): int
    {
        $pagesCount = $this->getTotal() / $this->getPerPage();
        if ($pagesCount * $this->getPerPage() < $this->getTotal()) {
            $pagesCount++;
        }

        return $pagesCount;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $currentPage): void
    {
        if ($currentPage < 1) {
            throw new InvalidArgumentException('invalid page');
        }
        // check validity as 0 based
        if (($currentPage - 1) * $this->getPerPage() > $this->getTotal()) {
            throw new InvalidArgumentException('invalid page');
        }
        $this->currentPage = $currentPage;
    }
}