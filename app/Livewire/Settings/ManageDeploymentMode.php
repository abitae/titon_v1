<?php

namespace App\Livewire\Settings;

use App\Actions\Deployment\ResetSystemMode;
use App\Concerns\InteractsWithToast;
use App\Services\Application\ApplicationSettingsManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ManageDeploymentMode extends Component
{
    use InteractsWithToast;

    public string $title = 'Produccion';

    public string $deploymentMode = ResetSystemMode::Development;

    public bool $showConfirmationModal = false;

    public ?string $targetMode = null;

    public string $confirmation = '';

    /**
     * @var array<string, int>
     */
    public array $summary = [];

    public function mount(ApplicationSettingsManager $applicationSettings, ResetSystemMode $resetSystemMode): void
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);

        $this->deploymentMode = $applicationSettings->current()->deployment_mode ?? ResetSystemMode::Development;
        $this->summary = $resetSystemMode->summary();
    }

    public function render(): View
    {
        return view('livewire.settings.manage-deployment-mode')
            ->layout('layouts.app', ['title' => $this->title]);
    }

    public function openProductionConfirmation(): void
    {
        $this->authorizeDeploymentEdit();
        $this->openConfirmation(ResetSystemMode::Production);
    }

    public function openDevelopmentConfirmation(): void
    {
        $this->authorizeDeploymentEdit();
        $this->openConfirmation(ResetSystemMode::Development);
    }

    public function closeConfirmation(): void
    {
        $this->showConfirmationModal = false;
        $this->targetMode = null;
        $this->confirmation = '';
        $this->resetValidation();
    }

    public function confirmModeChange(ResetSystemMode $resetSystemMode, ApplicationSettingsManager $applicationSettings): void
    {
        $this->authorizeDeploymentEdit();

        $expectedConfirmation = $this->expectedConfirmation();

        if ($this->confirmation !== $expectedConfirmation) {
            throw ValidationException::withMessages([
                'confirmation' => 'Escribe '.$expectedConfirmation.' para confirmar esta accion.',
            ]);
        }

        $mode = $this->targetMode;

        if (! is_string($mode)) {
            return;
        }

        $resetSystemMode->handle($mode);

        $this->deploymentMode = $applicationSettings->current()->deployment_mode ?? ResetSystemMode::Development;
        $this->summary = $resetSystemMode->summary();
        $this->closeConfirmation();

        $this->successToast(
            $mode === ResetSystemMode::Production
                ? 'Sistema preparado para produccion.'
                : 'Datos de desarrollo reinsertados correctamente.',
        );
    }

    public function targetModeLabel(): string
    {
        return $this->targetMode === ResetSystemMode::Production ? 'Produccion' : 'Desarrollo';
    }

    public function expectedConfirmation(): string
    {
        return $this->targetMode === ResetSystemMode::Production ? 'PRODUCCION' : 'DESARROLLO';
    }

    protected function openConfirmation(string $mode): void
    {
        $this->targetMode = $mode;
        $this->confirmation = '';
        $this->showConfirmationModal = true;
        $this->resetValidation();
    }

    protected function authorizeDeploymentEdit(): void
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);
        abort_unless(Auth::user()?->can('deployment.editar'), 403);
    }
}
