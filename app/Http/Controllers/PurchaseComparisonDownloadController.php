<?php

namespace App\Http\Controllers;

use App\Concerns\AppliesExportCorrelationStamp;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\SupplierContract;
use App\Reports\Contracts\SupplierContractPdfReport;
use App\Reports\Purchases\PurchaseOrderEntityPdfReport;
use App\Reports\Purchases\PurchaseOrderPdfReport;
use App\Reports\Purchases\QuotationComparisonPdfReport;
use App\Services\Audit\UserAuditLogger;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseComparisonDownloadController extends Controller
{
    use AppliesExportCorrelationStamp;

    public function comparison(PurchaseRequest $purchaseRequest, QuotationComparisonPdfReport $quotationComparisonPdfReport, UserAuditLogger $userAuditLogger): StreamedResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $pdf = $quotationComparisonPdfReport->build($purchaseRequest->load(['project', 'comparison.selectedQuotation.supplier', 'quotations.supplier', 'quotations.items']));

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Reportes',
            auditable: $purchaseRequest,
            observation: 'Exportacion PDF de comparativa de cotizaciones.',
        );

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf;
        }, $this->stampedExportFilename('comparativa-'.$purchaseRequest->code.'.pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function purchaseOrder(PurchaseRequest $purchaseRequest, PurchaseOrderPdfReport $purchaseOrderPdfReport, UserAuditLogger $userAuditLogger): StreamedResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $pdf = $purchaseOrderPdfReport->build($purchaseRequest->load(['project', 'comparison.selectedQuotation.supplier', 'comparison.selectedQuotation.items']));

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Reportes',
            auditable: $purchaseRequest,
            observation: 'Exportacion PDF de orden de compra proyectada.',
        );

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf;
        }, $this->stampedExportFilename('orden-compra-'.$purchaseRequest->code.'.pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function order(PurchaseOrder $purchaseOrder, PurchaseOrderEntityPdfReport $purchaseOrderEntityPdfReport, UserAuditLogger $userAuditLogger): StreamedResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $pdf = $purchaseOrderEntityPdfReport->build($purchaseOrder->load(['project', 'supplier', 'items']));

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Reportes',
            auditable: $purchaseOrder,
            observation: 'Exportacion PDF de orden de compra.',
        );

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf;
        }, $this->stampedExportFilename('orden-'.$purchaseOrder->code.'.pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function contract(SupplierContract $supplierContract, SupplierContractPdfReport $supplierContractPdfReport, UserAuditLogger $userAuditLogger): StreamedResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $pdf = $supplierContractPdfReport->build($supplierContract->load(['project', 'supplier', 'purchaseOrder']));

        $userAuditLogger->log(
            action: 'exportacion_pdf',
            module: 'Reportes',
            auditable: $supplierContract,
            observation: 'Exportacion PDF de contrato de proveedor.',
        );

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf;
        }, $this->stampedExportFilename('contrato-'.$supplierContract->contract_number.'.pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
