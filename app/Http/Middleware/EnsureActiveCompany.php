<?php

namespace App\Http\Middleware;

use App\Services\Companies\CompanyContext;
use App\Services\Ui\Toast;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveCompany
{
    public function __construct(
        protected CompanyContext $companyContext,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user !== null && $this->companyContext->resolveFor($user) === null) {
            Toast::flashWarning('Debes tener una empresa activa para continuar.');

            return redirect()->route('companies.index');
        }

        return $next($request);
    }

    protected function shouldBypass(Request $request): bool
    {
        if ($request->routeIs('settings.deployment-mode')) {
            return true;
        }

        if ($request->is('livewire*/update') && $request->user()?->can('deployment.editar')) {
            $components = $request->input('components', []);

            if (! is_array($components)) {
                return false;
            }

            foreach ($components as $component) {
                if (! is_array($component)) {
                    continue;
                }

                $snapshot = json_decode($component['snapshot'] ?? '', true);

                if (! is_array($snapshot)) {
                    continue;
                }

                $componentName = (string) ($snapshot['memo']['name'] ?? '');

                if (str_contains($componentName, 'manage-deployment-mode')
                    || str_contains($componentName, 'ManageDeploymentMode')) {
                    return true;
                }
            }
        }

        return false;
    }
}
