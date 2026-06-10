<?php

namespace App\Livewire\Mechanics;

use App\Concerns\ViewsPdfInModal;
use App\Services\Mechanics\MechanicalDashboardAnalytics;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ShowMechanicalDashboard extends Component
{
    use ViewsPdfInModal;

    public string $title = 'Mecanica';

    public function openMechanicsReportPdf(string $routeName, string $title): void
    {
        $this->openRoutePdfModal($routeName, $title);
    }

    public function render(MechanicalDashboardAnalytics $mechanicalDashboardAnalytics): View
    {
        $payload = $mechanicalDashboardAnalytics->build(auth()->user());

        return view('livewire.mechanics.show-mechanical-dashboard', [
            'kpis' => $payload['kpis'],
            'charts' => $payload['charts'],
            'alerts' => $payload['alerts'],
        ])->layout('layouts.app', ['title' => $this->title]);
    }
}
