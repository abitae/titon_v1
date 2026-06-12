<?php

namespace App\Providers;

use App\Http\Middleware\EnsureActiveCompany;
use App\Http\Middleware\SetActiveCompanyContext;
use App\Models\AccountsPayable;
use App\Models\Order;
use App\Models\PurchaseRequest;
use App\Models\SupplierContract;
use App\Policies\AccountsPayablePolicy;
use App\Policies\OrderPolicy;
use App\Policies\RequirementPolicy;
use App\Policies\SupplierContractPolicy;
use App\Services\Audit\UserAuditLogger;
use App\Services\Pdf\MpdfBuilder;
use App\Services\Pdf\PdfReportBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
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
        $this->app->bind(PdfReportBuilder::class, MpdfBuilder::class);
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

        Model::preventLazyLoading(! app()->isProduction());

        Gate::policy(PurchaseRequest::class, RequirementPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(AccountsPayable::class, AccountsPayablePolicy::class);
        Gate::policy(SupplierContract::class, SupplierContractPolicy::class);

        Gate::before(function ($user, string $ability): ?bool {
            return $user->hasRole('Super Admin') ? true : null;
        });

        $permissionAliases = [
            'requerimientos.ver' => 'purchases.ver',
            'requerimientos.crear' => 'purchases.crear',
            'requerimientos.editar' => 'purchases.editar',
            'requerimientos.cancelar' => 'purchases.eliminar',
            'requerimientos.enviar_proveedor' => 'purchases.aprobar',
            'cotizaciones.ver' => 'purchases.ver',
            'cotizaciones.crear' => 'purchases.crear',
            'cotizaciones.evaluar' => 'purchases.aprobar',
            'cotizaciones.seleccionar' => 'purchases.aprobar',
            'ordenes.ver' => 'purchases.ver',
            'ordenes.crear' => 'purchases.aprobar',
            'ordenes.emitir' => 'purchases.aprobar',
            'ordenes.anular' => 'purchases.aprobar',
            'ordenes.conformidad' => 'purchases.aprobar',
            'ordenes.rechazar' => 'purchases.aprobar',
            'cuentas_pagar.ver' => 'payments.ver',
            'cuentas_pagar.subir_documentos' => 'payments.crear',
            'cuentas_pagar.pagar' => 'payments.crear',
            'cuentas_pagar.exportar' => 'payments.exportar',
        ];

        foreach ($permissionAliases as $newPermission => $legacyPermission) {
            Gate::define($newPermission, fn ($user): bool => $user->can($legacyPermission) || $user->can($newPermission));
        }
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
