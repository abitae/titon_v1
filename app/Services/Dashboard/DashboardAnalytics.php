<?php

namespace App\Services\Dashboard;

use App\Enums\AccountsPayableStatus;
use App\Enums\DocumentStatus;
use App\Enums\OrderStatus;
use App\Enums\ProjectStatus;
use App\Enums\QuotationStatus;
use App\Enums\RequirementStatus;
use App\Models\AccountsPayable;
use App\Models\AccountsPayablePayment;
use App\Models\Company;
use App\Models\ContractPaymentSchedule;
use App\Models\Document;
use App\Models\Order;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Scopes\CurrentCompanyScope;
use App\Models\SupplierContract;
use App\Models\SupplierPayment;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class DashboardAnalytics
{
    public function __construct(
        protected CompanyContext $companyContext,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user, string $mode = 'company'): array
    {
        $availableCompanies = $this->companyContext->availableFor($user);
        $currentCompany = $this->companyContext->resolveFor($user);
        $canViewConsolidated = $this->canViewConsolidated($user, $availableCompanies);
        $resolvedMode = $mode === 'consolidated' && $canViewConsolidated ? 'consolidated' : 'company';
        $companyIds = $resolvedMode === 'consolidated'
            ? $availableCompanies->pluck('id')->all()
            : array_filter([$currentCompany?->id]);

        if ($companyIds === []) {
            return [
                'mode' => $resolvedMode,
                'can_view_consolidated' => $canViewConsolidated,
                'available_companies' => $availableCompanies,
                'current_company' => $currentCompany,
                'scope_label' => 'Sin empresa activa',
                'kpis' => $this->emptyKpis(),
                'charts' => $this->emptyCharts(),
                'highlights' => [],
            ];
        }

        $contractedTotal = (float) $this->baseQuery(SupplierContract::class, $companyIds)->sum('total_amount');
        $paidTotal = (float) $this->baseQuery(SupplierPayment::class, $companyIds)->sum('amount');
        $pendingBalance = max(0, $contractedTotal - $paidTotal);

        return [
            'mode' => $resolvedMode,
            'can_view_consolidated' => $canViewConsolidated,
            'available_companies' => $availableCompanies,
            'current_company' => $currentCompany,
            'scope_label' => $resolvedMode === 'consolidated'
                ? 'Consolidado de '.count($companyIds).' empresas'
                : ($currentCompany?->business_name ?? $currentCompany?->name ?? 'Empresa activa'),
            'kpis' => [
                'active_projects' => $this->baseQuery(Project::class, $companyIds)
                    ->whereNotIn('status', [ProjectStatus::Completed->value(), ProjectStatus::Closed->value()])
                    ->count(),
                'contracted_total' => $contractedTotal,
                'paid_total' => $paidTotal,
                'pending_balance' => $pendingBalance,
                'overdue_payments' => $this->baseQuery(ContractPaymentSchedule::class, $companyIds)
                    ->whereDate('due_date', '<', today())
                    ->where('balance', '>', 0)
                    ->count(),
                'active_contracts' => $this->baseQuery(SupplierContract::class, $companyIds)
                    ->whereIn('status', ['aprobado', 'firmado', 'en_ejecucion'])
                    ->count(),
                'requirements_draft' => $this->baseQuery(Requirement::class, $companyIds)
                    ->where('status', RequirementStatus::Draft->value())
                    ->count(),
                'requirements_in_process' => $this->baseQuery(Requirement::class, $companyIds)
                    ->where('status', RequirementStatus::InProcess->value())
                    ->count(),
                'quotations_pending_evaluation' => $this->baseQuery(SupplierQuotation::class, $companyIds)
                    ->where('status', QuotationStatus::Registered->value())
                    ->count(),
                'orders_pending_conformity' => $this->baseQuery(Order::class, $companyIds)
                    ->whereIn('status', [OrderStatus::Issued->value(), OrderStatus::Sent->value(), OrderStatus::InAttention->value(), OrderStatus::Attended->value()])
                    ->count(),
                'accounts_payable_pending' => $this->baseQuery(AccountsPayable::class, $companyIds)
                    ->whereIn('status', [AccountsPayableStatus::PendingDocuments->value(), AccountsPayableStatus::Observed->value()])
                    ->count(),
                'accounts_payable_ready' => $this->baseQuery(AccountsPayable::class, $companyIds)
                    ->where('status', AccountsPayableStatus::ReadyForPayment->value())
                    ->count(),
                'payments_month' => $this->baseQuery(AccountsPayablePayment::class, $companyIds)
                    ->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount'),
                'pending_requests' => $this->baseQuery(Requirement::class, $companyIds)
                    ->whereNotIn('status', [RequirementStatus::Attended->value(), RequirementStatus::Cancelled->value()])
                    ->count(),
                'expired_documents' => $this->baseQuery(Document::class, $companyIds)
                    ->whereDate('due_date', '<', today())
                    ->whereNotIn('status', [
                        DocumentStatus::Approved->value(),
                        DocumentStatus::Rejected->value(),
                        DocumentStatus::Closed->value(),
                    ])
                    ->count(),
            ],
            'charts' => [
                'projects_by_city' => $this->projectsByCityChart($companyIds),
                'payments_by_month' => $this->paymentsByMonthChart($companyIds),
                'contracts_by_status' => $this->contractsByStatusChart($companyIds),
                'contracted_vs_paid' => $this->contractedVsPaidChart($contractedTotal, $paidTotal),
                'top_suppliers' => $this->topSuppliersChart($companyIds),
            ],
            'highlights' => $this->highlights($companyIds),
        ];
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function baseQuery(string $modelClass, array $companyIds): Builder
    {
        /** @var Model $modelClass */
        return $modelClass::query()
            ->withoutGlobalScope(CurrentCompanyScope::class)
            ->whereIn('company_id', $companyIds);
    }

    /**
     * @param  Collection<int, Company>  $availableCompanies
     */
    protected function canViewConsolidated(User $user, Collection $availableCompanies): bool
    {
        if ($availableCompanies->count() < 2) {
            return false;
        }

        $roleIds = $availableCompanies
            ->pluck('pivot.role_id')
            ->filter()
            ->unique()
            ->all();

        return Role::query()
            ->whereIn('id', $roleIds)
            ->whereIn('name', ['Super Admin', 'Gerencia'])
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    protected function projectsByCityChart(array $companyIds): array
    {
        $rows = $this->baseQuery(Project::class, $companyIds)
            ->selectRaw('COALESCE(NULLIF(city, ""), "Sin ciudad") as city_name, COUNT(*) as total')
            ->groupBy('city_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $rows->pluck('city_name')->all(),
                'datasets' => [[
                    'label' => 'Obras',
                    'data' => $rows->pluck('total')->map(fn ($value): int => (int) $value)->all(),
                    'backgroundColor' => ['#0f766e', '#0891b2', '#0ea5e9', '#2563eb', '#4f46e5', '#7c3aed'],
                    'borderRadius' => 10,
                    'maxBarThickness' => 34,
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function paymentsByMonthChart(array $companyIds): array
    {
        $startMonth = now()->startOfMonth()->subMonths(5);
        $rows = $this->baseQuery(SupplierPayment::class, $companyIds)
            ->whereDate('payment_date', '>=', $startMonth)
            ->get(['payment_date', 'amount'])
            ->groupBy(fn (SupplierPayment $payment): string => $payment->payment_date?->format('Y-m') ?? 'unknown')
            ->map(fn (Collection $payments): float => (float) $payments->sum('amount'));

        $labels = [];
        $values = [];

        foreach (range(0, 5) as $index) {
            $month = $startMonth->copy()->addMonths($index);
            $key = $month->format('Y-m');
            $labels[] = ucfirst($month->translatedFormat('M Y'));
            $values[] = (float) ($rows[$key] ?? 0);
        }

        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Pagos',
                    'data' => $values,
                    'borderColor' => '#0f766e',
                    'backgroundColor' => 'rgba(15, 118, 110, 0.14)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function contractsByStatusChart(array $companyIds): array
    {
        $rows = $this->baseQuery(SupplierContract::class, $companyIds)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => $rows->pluck('status')->map(fn (string $status): string => str($status)->replace('_', ' ')->title()->toString())->all(),
                'datasets' => [[
                    'label' => 'Contratos',
                    'data' => $rows->pluck('total')->map(fn ($value): int => (int) $value)->all(),
                    'backgroundColor' => ['#0f766e', '#1d4ed8', '#7c3aed', '#f59e0b', '#ef4444', '#64748b'],
                    'borderWidth' => 0,
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function contractedVsPaidChart(float $contractedTotal, float $paidTotal): array
    {
        $pendingBalance = max(0, $contractedTotal - $paidTotal);

        return [
            'type' => 'bar',
            'data' => [
                'labels' => ['Contratado', 'Pagado', 'Pendiente'],
                'datasets' => [[
                    'label' => 'Monto',
                    'data' => [$contractedTotal, $paidTotal, $pendingBalance],
                    'backgroundColor' => ['#0f172a', '#0f766e', '#f59e0b'],
                    'borderRadius' => 12,
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function topSuppliersChart(array $companyIds): array
    {
        $rows = SupplierContract::query()
            ->withoutGlobalScope(CurrentCompanyScope::class)
            ->join('suppliers', 'suppliers.id', '=', 'supplier_contracts.supplier_id')
            ->whereIn('supplier_contracts.company_id', $companyIds)
            ->select('suppliers.business_name')
            ->selectRaw('SUM(supplier_contracts.total_amount) as total_amount')
            ->groupBy('suppliers.business_name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $rows->pluck('business_name')->all(),
                'datasets' => [[
                    'label' => 'Monto contratado',
                    'data' => $rows->pluck('total_amount')->map(fn ($value): float => (float) $value)->all(),
                    'backgroundColor' => ['#164e63', '#155e75', '#0e7490', '#0891b2', '#06b6d4'],
                    'borderRadius' => 10,
                ]],
            ],
            'options' => [
                'indexAxis' => 'y',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|float|int>>
     */
    protected function highlights(array $companyIds): array
    {
        $largestContract = $this->baseQuery(SupplierContract::class, $companyIds)
            ->with(['supplier', 'project'])
            ->orderByDesc('total_amount')
            ->first();

        $latestPayment = $this->baseQuery(SupplierPayment::class, $companyIds)
            ->with(['supplier', 'project'])
            ->latest('payment_date')
            ->first();

        $pendingQuotations = $this->baseQuery(SupplierQuotation::class, $companyIds)->count();

        return array_values(array_filter([
            $largestContract !== null ? [
                'label' => 'Mayor contrato',
                'value' => $largestContract->supplier?->business_name ?? 'Proveedor',
                'meta' => $largestContract->currency.' '.number_format((float) $largestContract->total_amount, 2),
            ] : null,
            $latestPayment !== null ? [
                'label' => 'Ultimo pago',
                'value' => $latestPayment->supplier?->business_name ?? 'Proveedor',
                'meta' => ($latestPayment->payment_date?->format('d/m/Y') ?? 'Sin fecha').' - '.$latestPayment->currency.' '.number_format((float) $latestPayment->amount, 2),
            ] : null,
            [
                'label' => 'Cotizaciones registradas',
                'value' => number_format($pendingQuotations),
                'meta' => 'Base disponible para comparativas y adjudicaciones.',
            ],
        ]));
    }

    /**
     * @return array<string, int|float>
     */
    protected function emptyKpis(): array
    {
        return [
            'active_projects' => 0,
            'contracted_total' => 0.0,
            'paid_total' => 0.0,
            'pending_balance' => 0.0,
            'overdue_payments' => 0,
            'active_contracts' => 0,
            'pending_requests' => 0,
            'expired_documents' => 0,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function emptyCharts(): array
    {
        return [
            'projects_by_city' => ['type' => 'bar', 'data' => ['labels' => [], 'datasets' => []]],
            'payments_by_month' => ['type' => 'line', 'data' => ['labels' => [], 'datasets' => []]],
            'contracts_by_status' => ['type' => 'doughnut', 'data' => ['labels' => [], 'datasets' => []]],
            'contracted_vs_paid' => ['type' => 'bar', 'data' => ['labels' => [], 'datasets' => []]],
            'top_suppliers' => ['type' => 'bar', 'data' => ['labels' => [], 'datasets' => []]],
        ];
    }
}
