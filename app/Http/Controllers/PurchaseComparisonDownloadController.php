<?php

namespace App\Http\Controllers;

use App\Concerns\AppliesExportCorrelationStamp;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\SupplierContract;
use App\Models\SupplierQuotation;
use App\Reports\Contracts\SupplierContractPdfReport;
use App\Reports\Purchases\PurchaseOrderEntityPdfReport;
use App\Reports\Purchases\PurchaseOrderPdfReport;
use App\Reports\Purchases\QuotationComparisonPdfReport;
use App\Reports\Purchases\SupplierQuotationPdfReport;
use App\Services\Audit\UserAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseComparisonDownloadController extends Controller
{
    use AppliesExportCorrelationStamp;

    public function quotationPreview(
        Request $request,
        SupplierQuotation $supplierQuotation,
        SupplierQuotationPdfReport $supplierQuotationPdfReport,
    ): BinaryFileResponse|Response {
        $this->authorizePurchasesView($request);

        $media = $supplierQuotation->getFirstMedia('cotizacion_pdf');

        if ($media !== null && is_file($media->getPath())) {
            return response()->file($media->getPath(), $this->inlinePdfHeaders($media->file_name));
        }

        $pdf = $supplierQuotationPdfReport->build($supplierQuotation);

        return $this->inlinePdfResponse($pdf, 'cotizacion-'.$supplierQuotation->code.'.pdf');
    }

    public function comparison(
        Request $request,
        PurchaseRequest $purchaseRequest,
        QuotationComparisonPdfReport $quotationComparisonPdfReport,
        UserAuditLogger $userAuditLogger,
    ): StreamedResponse {
        $this->authorizePurchasesExport($request);
        $this->authorize('view', $purchaseRequest);

        $pdf = $quotationComparisonPdfReport->build($purchaseRequest->load([
            'project',
            'comparison.selectedQuotation.supplier',
            'quotations.supplier',
            'quotations.items',
        ]));

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Reportes',
            auditable: $purchaseRequest,
            observation: 'Exportacion PDF de comparativa de cotizaciones.',
        );

        return $this->downloadPdfResponse($pdf, 'comparativa-'.$purchaseRequest->code.'.pdf');
    }

    public function purchaseOrder(
        Request $request,
        PurchaseRequest $purchaseRequest,
        PurchaseOrderPdfReport $purchaseOrderPdfReport,
        UserAuditLogger $userAuditLogger,
    ): StreamedResponse {
        $this->authorizePurchasesExport($request);
        $this->authorize('view', $purchaseRequest);

        $pdf = $purchaseOrderPdfReport->build($purchaseRequest->load([
            'project',
            'comparison.selectedQuotation.supplier',
            'comparison.selectedQuotation.items',
        ]));

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Reportes',
            auditable: $purchaseRequest,
            observation: 'Exportacion PDF de orden de compra proyectada.',
        );

        return $this->downloadPdfResponse($pdf, 'orden-compra-'.$purchaseRequest->code.'.pdf');
    }

    public function orderPreview(
        Request $request,
        PurchaseOrder $purchaseOrder,
        PurchaseOrderEntityPdfReport $purchaseOrderEntityPdfReport,
    ): Response {
        $this->authorizePurchasesView($request);

        $pdf = $purchaseOrderEntityPdfReport->build($purchaseOrder->load(['project', 'supplier', 'items']));

        return $this->inlinePdfResponse($pdf, 'orden-'.$purchaseOrder->code.'.pdf');
    }

    public function order(
        Request $request,
        PurchaseOrder $purchaseOrder,
        PurchaseOrderEntityPdfReport $purchaseOrderEntityPdfReport,
        UserAuditLogger $userAuditLogger,
    ): StreamedResponse {
        $this->authorizePurchasesExport($request);

        $pdf = $purchaseOrderEntityPdfReport->build($purchaseOrder->load(['project', 'supplier', 'items']));

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Reportes',
            auditable: $purchaseOrder,
            observation: 'Exportacion PDF de orden de compra.',
        );

        return $this->downloadPdfResponse($pdf, 'orden-'.$purchaseOrder->code.'.pdf');
    }

    public function contract(
        Request $request,
        SupplierContract $supplierContract,
        SupplierContractPdfReport $supplierContractPdfReport,
        UserAuditLogger $userAuditLogger,
    ): StreamedResponse {
        $this->authorizeContractsExport($request);

        $pdf = $supplierContractPdfReport->build($supplierContract->load(['project', 'supplier', 'order']));

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Reportes',
            auditable: $supplierContract,
            observation: 'Exportacion PDF de contrato de proveedor.',
        );

        return $this->downloadPdfResponse($pdf, 'contrato-'.$supplierContract->contract_number.'.pdf');
    }

    protected function authorizePurchasesView(Request $request): void
    {
        abort_unless($request->user()?->can('purchases.ver'), 403);
    }

    protected function authorizePurchasesExport(Request $request): void
    {
        abort_unless($request->user()?->can('purchases.exportar'), 403);
    }

    protected function authorizeContractsExport(Request $request): void
    {
        abort_unless($request->user()?->can('contracts.exportar'), 403);
    }

    /**
     * @return array<string, string>
     */
    protected function inlinePdfHeaders(string $filename): array
    {
        return [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->sanitizeFilename($filename).'"',
            'X-Frame-Options' => 'SAMEORIGIN',
        ];
    }

    protected function inlinePdfResponse(string $pdf, string $filename): Response
    {
        return response($pdf, 200, $this->inlinePdfHeaders($filename));
    }

    protected function downloadPdfResponse(string $pdf, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf;
        }, $this->stampedExportFilename($filename), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    protected function sanitizeFilename(string $filename): string
    {
        $sanitized = (string) preg_replace('/[^a-zA-Z0-9._-]+/', '-', $filename);

        return trim($sanitized, '-');
    }
}
