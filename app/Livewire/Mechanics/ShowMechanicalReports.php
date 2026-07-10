<?php

namespace App\Livewire\Mechanics;

use App\Concerns\ViewsPdfInModal;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ShowMechanicalReports extends Component
{
    use ViewsPdfInModal;

    public string $title = 'Reportes de mecanica';

    public function openMechanicsReportPdf(string $routeName, string $title): void
    {
        $this->openRoutePdfModal($routeName, $title);
    }

    public function render(): View
    {
        return view('livewire.mechanics.show-mechanical-reports')
            ->layout('layouts.app', ['title' => $this->title]);
    }
}
