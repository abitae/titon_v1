<?php

namespace App\Console\Commands;

use App\Actions\Deployment\ResetSystemMode;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

#[Signature('deployment:reset {mode : development|production} {--force : Ejecutar sin confirmacion interactiva}')]
#[Description('Reset system data for development or production deployment mode')]
class ResetDeploymentModeCommand extends Command
{
    public function handle(ResetSystemMode $resetSystemMode): int
    {
        $mode = strtolower((string) $this->argument('mode'));

        if (! in_array($mode, [ResetSystemMode::Development, ResetSystemMode::Production], true)) {
            $this->error('El modo debe ser development o production.');

            return self::FAILURE;
        }

        $label = $mode === ResetSystemMode::Production ? 'produccion' : 'desarrollo';

        if (! $this->option('force') && ! $this->confirm("Confirmas el cambio a modo {$label}? Esta accion modifica datos del sistema.")) {
            return self::SUCCESS;
        }

        try {
            $summary = $resetSystemMode->handle($mode);
        } catch (ValidationException $exception) {
            $this->error(collect($exception->errors())->flatten()->first() ?? 'No se pudo cambiar el modo.');

            return self::FAILURE;
        }

        $this->info($mode === ResetSystemMode::Production
            ? 'Sistema preparado para produccion.'
            : 'Datos de desarrollo reinsertados correctamente.');

        $this->table(
            ['Metrica', 'Antes del reset'],
            collect($summary)
                ->except('remaining_users')
                ->map(fn (int $count, string $label): array => [$label, $count])
                ->values()
                ->all(),
        );

        if (isset($summary['remaining_users'])) {
            $this->line('Usuarios restantes: '.$summary['remaining_users']);
        }

        return self::SUCCESS;
    }
}
