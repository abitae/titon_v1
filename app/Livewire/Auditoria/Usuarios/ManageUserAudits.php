<?php

namespace App\Livewire\Auditoria\Usuarios;

use App\Concerns\AppliesExportCorrelationStamp;
use App\Exports\AuditEntriesExport;
use App\Models\Audit;
use App\Models\Company;
use App\Models\User;
use App\Reports\Audit\UserAuditPdfReport;
use App\Services\Audit\UserAuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ManageUserAudits extends Component
{
    use AppliesExportCorrelationStamp, WithPagination;

    public string $title = 'Auditoria de usuarios';

    public string $search = '';

    public string $companyFilter = '';

    public string $userFilter = '';

    public string $moduleFilter = '';

    public string $actionFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function render(): View
    {
        abort_unless(auth()->user()->can('audits.ver'), 403);

        $query = $this->baseQuery();

        return view('livewire.auditoria.usuarios.manage-user-audits', [
            'audits' => $query->latest('created_at')->paginate(15),
            'companies' => $this->availableCompanies(),
            'users' => $this->availableUsers(),
            'modules' => Audit::query()
                ->where(fn (Builder $builder) => $builder->whereIn('company_id', $this->availableCompanyIds())->orWhereNull('company_id'))
                ->whereNotNull('module')
                ->distinct()
                ->orderBy('module')
                ->pluck('module'),
            'actions' => Audit::query()
                ->where(fn (Builder $builder) => $builder->whereIn('company_id', $this->availableCompanyIds())->orWhereNull('company_id'))
                ->whereNotNull('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, [
            'search',
            'companyFilter',
            'userFilter',
            'moduleFilter',
            'actionFilter',
            'dateFrom',
            'dateTo',
        ], true)) {
            $this->resetPage();
        }
    }

    public function exportExcel(UserAuditLogger $userAuditLogger): mixed
    {
        abort_unless(auth()->user()->can('audits.exportar'), 403);

        $userAuditLogger->log(
            action: 'exportacion_excel',
            module: 'Auditoria',
            auditable: auth()->user(),
            observation: 'Exportacion Excel de auditoria de usuarios.',
        );

        return Excel::download(
            new AuditEntriesExport($this->baseQuery()->latest('created_at')->get()),
            $this->stampedExportFilename('auditoria-usuarios.xlsx'),
        );
    }

    public function exportPdf(UserAuditPdfReport $userAuditPdfReport, UserAuditLogger $userAuditLogger): mixed
    {
        abort_unless(auth()->user()->can('audits.exportar'), 403);

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Auditoria',
            auditable: auth()->user(),
            observation: 'Exportacion PDF de auditoria de usuarios.',
        );

        $pdf = $userAuditPdfReport->build(
            $this->baseQuery()->latest('created_at')->limit(300)->get(),
            $this->filterSummary(),
        );

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf;
        }, $this->stampedExportFilename('auditoria-usuarios.pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    protected function baseQuery(): Builder
    {
        return Audit::query()
            ->with(['company', 'actor'])
            ->where(fn (Builder $builder) => $builder->whereIn('company_id', $this->availableCompanyIds())->orWhereNull('company_id'))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $nestedQuery): void {
                    $nestedQuery
                        ->where('user_name', 'like', '%'.$this->search.'%')
                        ->orWhere('module', 'like', '%'.$this->search.'%')
                        ->orWhere('action', 'like', '%'.$this->search.'%')
                        ->orWhere('auditable_type', 'like', '%'.$this->search.'%')
                        ->orWhere('observation', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->companyFilter !== '', fn (Builder $query) => $query->where('company_id', $this->companyFilter))
            ->when($this->userFilter !== '', fn (Builder $query) => $query->where('user_id', $this->userFilter))
            ->when($this->moduleFilter !== '', fn (Builder $query) => $query->where('module', $this->moduleFilter))
            ->when($this->actionFilter !== '', fn (Builder $query) => $query->where('action', $this->actionFilter))
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo));
    }

    protected function availableCompanies()
    {
        return Company::query()
            ->whereIn('id', $this->availableCompanyIds())
            ->orderBy('name')
            ->get();
    }

    protected function availableUsers()
    {
        return User::query()
            ->whereHas('companies', fn (Builder $query) => $query->whereIn('companies.id', $this->availableCompanyIds()))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<int>
     */
    protected function availableCompanyIds(): array
    {
        return auth()->user()
            ?->activeCompanies()
            ->pluck('companies.id')
            ->map(fn ($id): int => (int) $id)
            ->all() ?? [];
    }

    /**
     * @return array<string, string>
     */
    protected function filterSummary(): array
    {
        return [
            'empresa' => $this->companyFilter !== '' ? (Company::query()->find($this->companyFilter)?->name ?? 'Seleccion') : 'Todas',
            'usuario' => $this->userFilter !== '' ? (User::query()->find($this->userFilter)?->name ?? 'Seleccion') : 'Todos',
            'modulo' => $this->moduleFilter !== '' ? $this->moduleFilter : 'Todos',
            'accion' => $this->actionFilter !== '' ? $this->actionFilter : 'Todas',
            'desde' => $this->dateFrom !== '' ? $this->dateFrom : '-',
            'hasta' => $this->dateTo !== '' ? $this->dateTo : '-',
        ];
    }
}
