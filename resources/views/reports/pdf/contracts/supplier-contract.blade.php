<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Contrato de proveedor</title>
    @include('reports.pdf.partials.styles')
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>Contrato de proveedor</h1>
            <p>{{ $supplierContract->contract_number }} | {{ $supplierContract->project?->name ?? 'Sin obra' }}</p>
        </div>

        <table class="meta-grid" cellspacing="10">
            <tr>
                <td class="meta-card">
                    <span class="label">Proveedor</span>
                    <strong>{{ $supplierContract->supplier?->business_name ?? 'Sin proveedor' }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Tipo de contrato</span>
                    <strong>{{ $supplierContract->contract_type }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Vigencia</span>
                    <strong>{{ $supplierContract->start_date?->format('d/m/Y') ?? 'Sin fecha' }} al {{ $supplierContract->end_date?->format('d/m/Y') ?? 'Sin fecha' }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Monto</span>
                    <strong>{{ $supplierContract->currency }} {{ number_format((float) $supplierContract->total_amount, 2) }}</strong>
                </td>
            </tr>
        </table>

        <div class="section">
            <h2>Estado contractual</h2>
            <div class="summary-note">
                <div><strong>Estado:</strong> {{ str($supplierContract->status)->replace('_', ' ')->title() }}</div>
                <div><strong>Condiciones de pago:</strong> {{ $supplierContract->payment_conditions ?: 'Sin condiciones registradas' }}</div>
                <div><strong>Penalidades:</strong> {{ $supplierContract->penalties ?: 'Sin penalidades' }}</div>
                <div><strong>Garant&iacute;as:</strong> {{ $supplierContract->guarantees ?: 'Sin garant&iacute;as' }}</div>
                <div><strong>Observaci&oacute;n:</strong> {{ $supplierContract->observation ?: 'Sin observaciones' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
