<?php

namespace App\Services\Ui;

class Toast
{
    /**
     * @return array{duration: int, slots: array<string, string>, dataset: array<string, string>}
     */
    public static function make(
        string $text,
        ?string $heading = null,
        int $duration = 5000,
        ?string $variant = null,
        ?string $position = null,
    ): array {
        $toast = [
            'duration' => $duration,
            'slots' => ['text' => $text],
            'dataset' => [],
        ];

        if ($heading !== null) {
            $toast['slots']['heading'] = $heading;
        }

        if ($variant !== null) {
            $toast['dataset']['variant'] = $variant;
        }

        if ($position !== null) {
            $toast['dataset']['position'] = $position;
        }

        return $toast;
    }

    /**
     * @return array{duration: int, slots: array<string, string>, dataset: array<string, string>}
     */
    public static function success(string $text, ?string $heading = null, int $duration = 5000): array
    {
        return self::make($text, $heading, $duration, 'success');
    }

    /**
     * @return array{duration: int, slots: array<string, string>, dataset: array<string, string>}
     */
    public static function warning(string $text, ?string $heading = null, int $duration = 5000): array
    {
        return self::make($text, $heading, $duration, 'warning');
    }

    /**
     * @return array{duration: int, slots: array<string, string>, dataset: array<string, string>}
     */
    public static function danger(string $text, ?string $heading = null, int $duration = 5000): array
    {
        return self::make($text, $heading, $duration, 'danger');
    }

    public static function flash(array $toast): void
    {
        $toasts = session()->get('toasts', []);
        $toasts[] = $toast;

        session()->flash('toasts', $toasts);
    }

    public static function flashSuccess(string $text, ?string $heading = null, int $duration = 5000): void
    {
        self::flash(self::success($text, $heading, $duration));
    }

    public static function flashWarning(string $text, ?string $heading = null, int $duration = 5000): void
    {
        self::flash(self::warning($text, $heading, $duration));
    }

    public static function flashDanger(string $text, ?string $heading = null, int $duration = 5000): void
    {
        self::flash(self::danger($text, $heading, $duration));
    }
}
