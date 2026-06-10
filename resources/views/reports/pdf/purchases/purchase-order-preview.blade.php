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
            <h1>Orden de compra proyectada</h1>
            <p>{{ $comparison?->order_code ?? 'Sin generar' }} | {{ $purchaseRequest->project?->name ?? 'Sin obra' }}</p>
        </div>

        <table class="meta-grid" cellspacing="10">
            <tr>
                <td class="meta-card">
                    <span class="label">Solicitud</span>
                    <strong>{{ $purchaseRequest->code }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Proveedor</span>
                    <strong>{{ $quotation?->supplier?->business_name ?? 'Sin proveedor' }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Total</span>
                    <strong>{{ $quotation?->currency ?? 'PEN' }} {{ number_format((float) ($quotation?->total ?? 0), 2) }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Entrega</span>
                    <strong>{{ (int) ($quotation?->delivery_time ?? 0) }} d&iacute;as</strong>
                </td>
            </tr>
        </table>

        <div class="section">
            <h2>Resumen comercial</h2>
            <div class="summary-note">
                <div><strong>Pago:</strong> {{ $quotation?->payment_conditions ?: 'Sin condici&oacute;n' }}</div>
                <div><strong>Garant&iacute;a:</strong> {{ $quotation?->warranty ?: 'Sin garant&iacute;a' }}</div>
                <div><strong>Motivo de selecci&oacute;n:</strong> {{ $comparison?->selection_reason ?: 'Sin motivo registrado' }}</div>
            </div>
        </div>

        <div class="section">
            <h2>Items adjudicados</h2>
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
                    @php
                        $previewItems = ($quotation?->items?->isNotEmpty() ?? false)
                            ? $quotation->items
                            : ($purchaseRequest->items ?? collect());
                    @endphp
                    @forelse ($previewItems as $item)
                        <tr>
                            <td>{{ $item->product_or_service ?? $item->description }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>
                                {{ $quotation?->currency ?? 'PEN' }}
                                {{ number_format((float) ($item->total ?? (($item->estimated_unit_price ?? 0) * $item->quantity)), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Sin ítems registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
