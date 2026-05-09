<?php

namespace App\Http\Middleware;

use App\Services\Companies\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveCompanyContext
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

        if ($user !== null) {
            $activeCompany = $this->companyContext->resolveFor($user);
            $request->attributes->set('activeCompany', $activeCompany);
            view()->share('activeCompany', $activeCompany);
        }

        return $next($request);
    }
}
