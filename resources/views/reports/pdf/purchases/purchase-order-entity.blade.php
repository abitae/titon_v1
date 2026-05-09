<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de compra</title>
    @include('reports.pdf.partials.styles')
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>Orden de compra</h1>
            <p>{{ $purchaseOrder->code }} | {{ $purchaseOrder->project?->name ?? 'Sin obra' }}</p>
        </div>

        <table class="meta-grid" cellspacing="10">
            <tr>
                <td class="meta-card">
                    <span class="label">Proveedor</span>
                    <strong>{{ $purchaseOrder->supplier?->business_name ?? 'Sin proveedor' }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Fecha de emisi&oacute;n</span>
                    <strong>{{ $purchaseOrder->issue_date?->format('d/m/Y') ?? 'Sin fecha' }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Estado</span>
                    <strong>{{ str($purchaseOrder->status)->replace('_', ' ')->title() }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Total</span>
                    <strong>{{ $purchaseOrder->currency }} {{ number_format((float) $purchaseOrder->total, 2) }}</strong>
                </td>
            </tr>
        </table>

        <div class="section">
            <h2>Condiciones</h2>
            <div class="summary-note">
                <div><strong>Condiciones:</strong> {{ $purchaseOrder->conditions ?: 'Sin condiciones registradas' }}</div>
                <div><strong>Observaci&oacute;n:</strong> {{ $purchaseOrder->observation ?: 'Sin observaciones' }}</div>
            </div>
        </div>

        <div class="section">
            <h2>Detalle de items</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto o servicio</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseOrder->items as $item)
                        <tr>
                            <td>{{ $item->product_or_service }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>{{ $purchaseOrder->currency }} {{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
