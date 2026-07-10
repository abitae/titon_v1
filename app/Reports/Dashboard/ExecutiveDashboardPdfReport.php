<?php

namespace App\Reports\Dashboard;

use App\Models\User;
use App\Services\Dashboard\DashboardAnalytics;
use App\Services\Pdf\MpdfBuilder;

class ExecutiveDashboardPdfReport
{
    public function __construct(
        protected DashboardAnalytics $dashboardAnalytics,
        protected MpdfBuilder $mpdfBuilder,
    ) {}

    public function build(User $user, string $mode = 'company'): string
    {
        $analytics = $this->dashboardAnalytics->build($user, $mode);

        return $this->mpdfBuilder->buildFromView('reports.pdf.dashboard.executive-summary', [
            'user' => $user,
            'analytics' => $analytics,
        ], 'Resumen ejecutivo', $user);
    }
}
