<?php

namespace App\Concerns;

use App\Services\Ui\Toast;
use Flux\Flux;
use Illuminate\Validation\ValidationException;

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

    /**
     * @param  array<string, mixed>  $rules
     * @param  array<string, string>  $messages
     * @param  array<string, string>  $attributes
     * @return array<string, mixed>
     */
    protected function validateWithToastFeedback(
        array $rules,
        array $messages = [],
        array $attributes = [],
        ?string $heading = 'Revisa el formulario',
    ): array {
        try {
            return $this->validate($rules, $messages, $attributes);
        } catch (ValidationException $exception) {
            $this->dangerToast($this->firstValidationErrorMessage($exception), $heading);

            throw $exception;
        }
    }

    protected function firstValidationErrorMessage(ValidationException $exception): string
    {
        return collect($exception->validator->errors()->all())->first()
            ?? 'Corrige los campos marcados e intenta nuevamente.';
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
