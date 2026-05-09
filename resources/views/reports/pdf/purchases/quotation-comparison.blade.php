<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comparativa de cotizaciones</title>
    @include('reports.pdf.partials.styles')
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>Comparativa de cotizaciones</h1>
            <p>Solicitud {{ $purchaseRequest->code }} | {{ $purchaseRequest->project?->name ?? 'Sin obra' }}</p>
        </div>

        <table class="meta-grid" cellspacing="10">
            <tr>
                <td class="meta-card">
                    <span class="label">Fecha de solicitud</span>
                    <strong>{{ $purchaseRequest->request_date?->format('d/m/Y') ?? 'Sin fecha' }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Estado</span>
                    <strong>{{ str($purchaseRequest->status)->replace('_', ' ')->title() }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Mejor precio</span>
                    <strong>{{ number_format((float) ($summary['min_total'] ?? 0), 2) }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Mejor entrega</span>
                    <strong>{{ (int) ($summary['min_delivery_time'] ?? 0) }} d&iacute;as</strong>
                </td>
            </tr>
        </table>

        <div class="section">
            <h2>Cotizaciones evaluadas</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>C&oacute;digo</th>
                        <th>Total</th>
                        <th>Entrega</th>
                        <th>Pago</th>
                        <th>Garant&iacute;a</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['quotations'] as $quotation)
                        <tr>
                            <td>{{ $quotation->supplier?->business_name ?? 'Sin proveedor' }}</td>
                            <td>{{ $quotation->code }}</td>
                            <td>{{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</td>
                            <td>{{ (int) $quotation->delivery_time }} d&iacute;as</td>
                            <td>{{ $quotation->payment_conditions ?: 'Sin condici&oacute;n' }}</td>
                            <td>{{ $quotation->warranty ?: 'Sin garant&iacute;a' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($purchaseRequest->comparison?->selectedQuotation !== null)
            <div class="section">
                <h2>Proveedor ganador</h2>
                <div class="summary-note">
                    <span class="badge">Adjudicado</span>
                    <div style="font-size: 16px; font-weight: bold; margin-top: 8px;">
                        {{ $purchaseRequest->comparison->selectedQuotation->supplier?->business_name ?? 'Sin proveedor' }}
                    </div>
                    <div class="muted" style="margin-top: 4px;">
                        Motivo: {{ $purchaseRequest->comparison->selection_reason ?: 'Sin motivo registrado' }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
