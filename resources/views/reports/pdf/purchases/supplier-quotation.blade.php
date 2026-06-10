<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotizaci&oacute;n {{ $quotation->code }}</title>
    @include('reports.pdf.partials.styles')
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>Cotizaci&oacute;n de proveedor</h1>
            <p>{{ $quotation->code }} | {{ $purchaseRequest?->project?->name ?? 'Sin obra' }} | {{ $quotation->supplier?->business_name ?? 'Sin proveedor' }}</p>
        </div>

        <table class="meta-grid" cellspacing="10">
            <tr>
                <td class="meta-card">
                    <span class="label">Solicitud</span>
                    <strong>{{ $purchaseRequest?->code ?? 'Sin solicitud' }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Fecha</span>
                    <strong>{{ $quotation->quotation_date?->format('d/m/Y') ?? 'Sin fecha' }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Vigencia</span>
                    <strong>{{ $quotation->valid_until?->format('d/m/Y') ?? 'Sin vigencia' }}</strong>
                </td>
                <td class="meta-card">
                    <span class="label">Entrega</span>
                    <strong>{{ (int) ($quotation->delivery_time_days ?? 0) }} d&iacute;as</strong>
                </td>
            </tr>
        </table>

        <div class="section">
            <h2>Resumen econ&oacute;mico</h2>
            <table class="meta-grid" cellspacing="10">
                <tr>
                    <td class="meta-card">
                        <span class="label">Subtotal</span>
                        <strong>{{ $quotation->currency }} {{ number_format((float) $quotation->subtotal, 2) }}</strong>
                    </td>
                    <td class="meta-card">
                        <span class="label">IGV / impuesto</span>
                        <strong>{{ $quotation->currency }} {{ number_format((float) $quotation->tax, 2) }}</strong>
                    </td>
                    <td class="meta-card">
                        <span class="label">Total</span>
                        <strong>{{ $quotation->currency }} {{ number_format((float) $quotation->total, 2) }}</strong>
                    </td>
                    <td class="meta-card">
                        <span class="label">Origen</span>
                        <strong>{{ $quotation->isPdfCapture() ? 'Archivo PDF' : 'Formulario' }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Condiciones comerciales</h2>
            <div class="summary-note">
                <div><strong>Condiciones de pago:</strong> {{ $quotation->payment_conditions ?: 'Sin condici&oacute;n' }}</div>
                <div><strong>Garant&iacute;a:</strong> {{ $quotation->warranty ?: 'Sin garant&iacute;a' }}</div>
                @if ($quotation->observation)
                    <div><strong>Observaci&oacute;n:</strong> {{ $quotation->observation }}</div>
                @endif
            </div>
        </div>

        <div class="section">
            <h2>&Iacute;tems cotizados</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto o servicio</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>P. unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotation->items as $item)
                        <tr>
                            <td>{{ $item->product_or_service }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>{{ $quotation->currency }} {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>{{ $quotation->currency }} {{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">Sin &iacute;tems registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
