<?php

namespace App\Livewire\Mechanics;

use App\Services\Mechanics\MechanicalDashboardAnalytics;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ShowMechanicalDashboard extends Component
{
    public string $title = 'Panel de mecanica';

    public function render(MechanicalDashboardAnalytics $mechanicalDashboardAnalytics): View
    {
        $payload = $mechanicalDashboardAnalytics->build(auth()->user());

        return view('livewire.mechanics.show-mechanical-dashboard', [
            'charts' => $payload['charts'],
            'kpis' => $payload['kpis'],
            'alerts' => $payload['alerts'],
        ])->layout('layouts.app', ['title' => $this->title]);
    }
}
