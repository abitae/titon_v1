<?php

namespace App\Concerns;

trait ShowsQuotationComparisonModal
{
    public bool $showComparisonModal = false;

    public function openComparisonModal(): void
    {
        $this->showComparisonModal = true;
    }

    public function closeComparisonModal(): void
    {
        $this->showComparisonModal = false;
    }
}
