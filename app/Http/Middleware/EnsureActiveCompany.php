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
        $user = $request->user();

        if ($user !== null && $this->companyContext->resolveFor($user) === null) {
            Toast::flashWarning('Debes tener una empresa activa para continuar.');

            return redirect()->route('companies.index');
        }

        return $next($request);
    }
}
