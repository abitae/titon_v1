<?php

namespace App\Livewire\Orders;

use App\Actions\Orders\RecordOrderConformity as RecordOrderConformityAction;
use App\Concerns\InteractsWithToast;
use App\Enums\ConformityResult;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RecordOrderConformity extends Component
{
    use InteractsWithToast;

    public string $title = 'Conformidad en obra';

    public Order $order;

    public string $result = 'conforme';

    public string $observation = '';

    public string $conformity_date = '';

    public function mount(Order $order): void
    {
        $this->order = $order->load(['project', 'supplier', 'requirement']);
        $this->conformity_date = now()->toDateString();
    }

    public function render(): View
    {
        return view('livewire.orders.record-order-conformity')
            ->layout('layouts.app', ['title' => $this->title]);
    }

    public function save(RecordOrderConformityAction $action): void
    {
        abort_unless(auth()->user()->can('ordenes.conformidad') || auth()->user()->can('ordenes.rechazar') || auth()->user()->can('purchases.aprobar'), 403);

        $validated = $this->validate([
            'result' => ['required', Rule::in([ConformityResult::Conform->value(), ConformityResult::Rejected->value()])],
            'observation' => ['nullable', 'string'],
            'conformity_date' => ['required', 'date'],
        ]);

        $action->handle(
            $this->order,
            auth()->user(),
            $validated['result'],
            $validated['observation'] ?: null,
            $validated['conformity_date'],
        );

        $this->successToast('Conformidad registrada.');
        $this->redirect(route('purchases.orders'), navigate: true);
    }
}
