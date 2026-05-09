<?php

namespace App\Providers;

use App\Http\Middleware\EnsureActiveCompany;
use App\Http\Middleware\SetActiveCompanyContext;
use App\Services\Audit\UserAuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerAuditEvents();
        $this->configureLivewireMiddleware();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        /** @var Kernel $kernel */
        $kernel = app(Kernel::class);

        $kernel->addToMiddlewarePriorityBefore(
            SetActiveCompanyContext::class,
            SubstituteBindings::class,
        );

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        Gate::before(function ($user, string $ability): ?bool {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }

    protected function registerAuditEvents(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            if (! $event->user instanceof Authenticatable) {
                return;
            }

            app(UserAuditLogger::class)->log(
                action: 'inicio_sesion',
                module: 'Seguridad',
                auditable: $event->user,
                newValues: ['guard' => $event->guard, 'remember' => $event->remember],
                observation: 'Inicio de sesion exitoso.',
                actor: $event->user,
            );
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if (! $event->user instanceof Authenticatable) {
                return;
            }

            app(UserAuditLogger::class)->log(
                action: 'cierre_sesion',
                module: 'Seguridad',
                auditable: $event->user,
                oldValues: ['guard' => $event->guard],
                observation: 'Cierre de sesion.',
                actor: $event->user,
            );
        });
    }

    protected function configureLivewireMiddleware(): void
    {
        Livewire::addPersistentMiddleware([
            SetActiveCompanyContext::class,
            EnsureActiveCompany::class,
        ]);
    }
}
