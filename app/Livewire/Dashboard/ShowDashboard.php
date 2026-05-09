<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardAnalytics;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ShowDashboard extends Component
{
    public string $title = 'Dashboard';

    public string $subtitle = 'Vista gerencial multiempresa con foco en obras, compras, contratos y pagos.';

    public string $mode = 'company';

    public function setMode(string $mode): void
    {
        $this->mode = in_array($mode, ['company', 'consolidated'], true) ? $mode : 'company';
    }

    public function render(DashboardAnalytics $dashboardAnalytics): View
    {
        $analytics = $dashboardAnalytics->build(auth()->user(), $this->mode);
        $this->mode = $analytics['mode'];

        return view('livewire.dashboard.show-dashboard')
            ->with('analytics', $analytics)
            ->layout('layouts.app', ['title' => $this->title]);
    }
}
