<?php

namespace App\Http\Controllers;

use App\Concerns\AppliesExportCorrelationStamp;
use App\Reports\Dashboard\ExecutiveDashboardPdfReport;
use App\Services\Audit\UserAuditLogger;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportDownloadController extends Controller
{
    use AppliesExportCorrelationStamp;

    public function dashboard(Request $request, ExecutiveDashboardPdfReport $executiveDashboardPdfReport, UserAuditLogger $userAuditLogger): StreamedResponse
    {
        abort_unless(auth()->check() && auth()->user()->can('dashboard.ver'), 403);

        $mode = $request->string('mode', 'company')->toString();
        $pdf = $executiveDashboardPdfReport->build(auth()->user(), $mode);

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Reportes',
            auditable: auth()->user(),
            newValues: ['modo' => $mode],
            observation: 'Exportacion PDF de resumen ejecutivo.',
        );

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf;
        }, $this->stampedExportFilename('resumen-ejecutivo-'.$mode.'.pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
