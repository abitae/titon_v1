<?php

namespace App\Livewire\Warehouse;

use App\Actions\Warehouse\RecordWarehouseOutbound;
use App\Actions\Warehouse\TransferWarehouseBetweenProjects;
use App\Concerns\InteractsWithToast;
use App\Enums\WarehouseItemType;
use App\Enums\WarehouseMovementSource;
use App\Models\Project;
use App\Models\User;
use App\Models\WarehouseMovement;
use App\Models\WarehouseStockItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ManageWarehouse extends Component
{
    use InteractsWithToast, WithPagination;

    public string $title = 'Almacen general';

    public string $activeTab = 'stock';

    public ?int $filter_work_project_id = null;

    public ?int $filter_responsible_user_id = null;

    public ?string $filter_item_type = null;

    public string $filter_description = '';

    public string $filter_source = '';

    public string $filter_transfer_code = '';

    public string $filter_order_code = '';

    public ?string $filter_date_from = null;

    public ?string $filter_date_to = null;

    public bool $showOutboundModal = false;

    public bool $showTransferModal = false;

    public bool $showKardexModal = false;

    public ?int $selected_stock_item_id = null;

    public string $outbound_quantity = '1';

    public string $outbound_reference = '';

    public string $outbound_date = '';

    public ?int $transfer_destination_project_id = null;

    public string $transfer_quantity = '1';

    public string $transfer_reference = '';

    public string $transfer_date = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('almacen.ver'), 403);
        $this->outbound_date = now()->toDateString();
        $this->transfer_date = now()->toDateString();
    }

    public function updatedFilterWorkProjectId(): void
    {
        $this->resetPage();
    }

    public function updatedFilterResponsibleUserId(): void
    {
        $this->resetPage();
    }

    public function updatedFilterItemType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDescription(): void
    {
        $this->resetPage();
    }

    public function updatedFilterSource(): void
    {
        $this->resetPage();
    }

    public function updatedFilterTransferCode(): void
    {
        $this->resetPage();
    }

    public function updatedFilterOrderCode(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDateTo(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filter_work_project_id = null;
        $this->filter_responsible_user_id = null;
        $this->filter_item_type = null;
        $this->filter_description = '';
        $this->filter_source = '';
        $this->filter_transfer_code = '';
        $this->filter_order_code = '';
        $this->filter_date_from = null;
        $this->filter_date_to = null;
        $this->resetPage();
    }

    public function openOutboundModal(int $stockItemId): void
    {
        abort_unless(auth()->user()?->can('almacen.mover'), 403);
        $this->selected_stock_item_id = $stockItemId;
        $this->outbound_quantity = '1';
        $this->outbound_reference = '';
        $this->outbound_date = now()->toDateString();
        $this->showOutboundModal = true;
    }

    public function openTransferModal(int $stockItemId): void
    {
        abort_unless(auth()->user()?->can('almacen.transferir'), 403);
        $this->selected_stock_item_id = $stockItemId;
        $this->transfer_destination_project_id = null;
        $this->transfer_quantity = '1';
        $this->transfer_reference = '';
        $this->transfer_date = now()->toDateString();
        $this->showTransferModal = true;
    }

    public function openKardexModal(int $stockItemId): void
    {
        $this->selected_stock_item_id = $stockItemId;
        $this->showKardexModal = true;
    }

    public function closeOutboundModal(): void
    {
        $this->showOutboundModal = false;
        $this->selected_stock_item_id = null;
    }

    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->selected_stock_item_id = null;
    }

    public function closeKardexModal(): void
    {
        $this->showKardexModal = false;
        $this->selected_stock_item_id = null;
    }

    public function saveOutbound(RecordWarehouseOutbound $recordWarehouseOutbound): void
    {
        abort_unless(auth()->user()?->can('almacen.mover'), 403);

        $validated = $this->validate([
            'selected_stock_item_id' => ['required', 'integer', 'exists:warehouse_stock_items,id'],
            'outbound_quantity' => ['required', 'numeric', 'min:0.001'],
            'outbound_date' => ['required', 'date'],
            'outbound_reference' => ['nullable', 'string', 'max:500'],
        ]);

        $stockItem = WarehouseStockItem::query()->findOrFail($validated['selected_stock_item_id']);

        $recordWarehouseOutbound->handle($stockItem, auth()->user(), [
            'quantity' => (string) $validated['outbound_quantity'],
            'movement_date' => $validated['outbound_date'],
            'reference' => $validated['outbound_reference'] ?: null,
        ]);

        $this->successToast('Salida registrada correctamente.');
        $this->closeOutboundModal();
    }

    public function saveTransfer(TransferWarehouseBetweenProjects $transferWarehouseBetweenProjects): void
    {
        abort_unless(auth()->user()?->can('almacen.transferir'), 403);

        $validated = $this->validate([
            'selected_stock_item_id' => ['required', 'integer', 'exists:warehouse_stock_items,id'],
            'transfer_destination_project_id' => ['required', 'integer', 'exists:projects,id'],
            'transfer_quantity' => ['required', 'numeric', 'min:0.001'],
            'transfer_date' => ['required', 'date'],
            'transfer_reference' => ['nullable', 'string', 'max:500'],
        ]);

        $stockItem = WarehouseStockItem::query()->findOrFail($validated['selected_stock_item_id']);

        $transferWarehouseBetweenProjects->handle($stockItem, auth()->user(), [
            'destination_work_project_id' => (int) $validated['transfer_destination_project_id'],
            'quantity' => (string) $validated['transfer_quantity'],
            'transfer_date' => $validated['transfer_date'],
            'reference' => $validated['transfer_reference'] ?: null,
        ]);

        $this->successToast('Transferencia registrada correctamente.');
        $this->closeTransferModal();
    }

    public function render(): View
    {
        $projects = Project::query()->select(['id', 'code', 'name'])->orderBy('code')->get();
        $users = User::query()->orderBy('name')->get(['id', 'name']);

        $selectedStockItem = $this->selected_stock_item_id
            ? WarehouseStockItem::query()->with(['project', 'movements' => fn ($q) => $q->latest()->limit(20)])->find($this->selected_stock_item_id)
            : null;

        return view('livewire.warehouse.manage-warehouse', [
            'stockItems' => $this->activeTab === 'stock'
                ? $this->stockItemsQuery()->paginate(15)
                : collect(),
            'movements' => $this->activeTab === 'kardex'
                ? $this->movementsQuery()->paginate(20)
                : collect(),
            'projects' => $projects,
            'users' => $users,
            'itemTypes' => WarehouseItemType::cases(),
            'movementSources' => WarehouseMovementSource::cases(),
            'selectedStockItem' => $selectedStockItem,
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    protected function stockItemsQuery(): Builder
    {
        return WarehouseStockItem::query()
            ->with(['project', 'supplier'])
            ->when($this->filter_work_project_id, fn (Builder $q) => $q->where('work_project_id', $this->filter_work_project_id))
            ->when($this->filter_item_type, fn (Builder $q) => $q->where('item_type', $this->filter_item_type))
            ->when(trim($this->filter_description) !== '', fn (Builder $q) => $q->where('description', 'like', '%'.trim($this->filter_description).'%'))
            ->orderByDesc('updated_at');
    }

    protected function movementsQuery(): Builder
    {
        return WarehouseMovement::query()
            ->with(['stockItem.project', 'responsible', 'order', 'transfer'])
            ->when($this->filter_work_project_id, function (Builder $q): void {
                $q->whereHas('stockItem', fn (Builder $sq) => $sq->where('work_project_id', $this->filter_work_project_id));
            })
            ->when($this->filter_responsible_user_id, fn (Builder $q) => $q->where('responsible_user_id', $this->filter_responsible_user_id))
            ->when($this->filter_source !== '', fn (Builder $q) => $q->where('source', $this->filter_source))
            ->when(trim($this->filter_description) !== '', function (Builder $q): void {
                $q->whereHas('stockItem', fn (Builder $sq) => $sq->where('description', 'like', '%'.trim($this->filter_description).'%'));
            })
            ->when(trim($this->filter_transfer_code) !== '', function (Builder $q): void {
                $q->whereHas('transfer', fn (Builder $tq) => $tq->where('transfer_code', 'like', '%'.trim($this->filter_transfer_code).'%'));
            })
            ->when(trim($this->filter_order_code) !== '', function (Builder $q): void {
                $q->whereHas('order', fn (Builder $oq) => $oq->where('code', 'like', '%'.trim($this->filter_order_code).'%'));
            })
            ->when($this->filter_date_from, fn (Builder $q) => $q->whereDate('movement_date', '>=', $this->filter_date_from))
            ->when($this->filter_date_to, fn (Builder $q) => $q->whereDate('movement_date', '<=', $this->filter_date_to))
            ->orderByDesc('movement_date')
            ->orderByDesc('id');
    }
}
