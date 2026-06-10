<?php

namespace App\Livewire\AccountsPayable;

use App\Concerns\InteractsWithToast;
use App\Enums\AccountsPayableStatus;
use App\Models\AccountsPayable;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ManageAccountsPayable extends Component
{
    use InteractsWithToast, WithPagination;

    public string $title = 'Cuentas por pagar';

    public string $search = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $accounts = AccountsPayable::query()
            ->with(['supplier', 'project', 'order'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nested): void {
                    $nested->where('code', 'like', '%'.$this->search.'%')
                        ->orWhereHas('supplier', fn ($q) => $q->where('business_name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.accounts-payable.manage-accounts-payable', [
            'accounts' => $accounts,
            'statusOptions' => AccountsPayableStatus::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }
}
