<?php

namespace App\Concerns;

use App\Services\Ui\Toast;
use Flux\Flux;

trait InteractsWithToast
{
    protected function successToast(string $text, ?string $heading = null, int $duration = 5000): void
    {
        Flux::toast(text: $text, heading: $heading, duration: $duration, variant: 'success');
    }

    protected function warningToast(string $text, ?string $heading = null, int $duration = 5000): void
    {
        Flux::toast(text: $text, heading: $heading, duration: $duration, variant: 'warning');
    }

    protected function dangerToast(string $text, ?string $heading = null, int $duration = 5000): void
    {
        Flux::toast(text: $text, heading: $heading, duration: $duration, variant: 'danger');
    }

    protected function flashSuccessToast(string $text, ?string $heading = null, int $duration = 5000): void
    {
        Toast::flashSuccess($text, $heading, $duration);
    }

    protected function flashWarningToast(string $text, ?string $heading = null, int $duration = 5000): void
    {
        Toast::flashWarning($text, $heading, $duration);
    }

    protected function flashDangerToast(string $text, ?string $heading = null, int $duration = 5000): void
    {
        Toast::flashDanger($text, $heading, $duration);
    }
}
