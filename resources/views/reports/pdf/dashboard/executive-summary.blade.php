<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resumen ejecutivo</title>
    @include('reports.pdf.partials.styles')
</head>
<body>
    @php
        $kpis = $analytics['kpis'];
        $currency = fn (float $amount): string => 'S/ '.number_format($amount, 2);
    @endphp

    <div class="page">
        <div class="hero">
            <h1>Resumen ejecutivo</h1>
            <p>{{ $analytics['scope_label'] }} | Generado para {{ $user->name }} el {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <table class="kpi-grid" cellspacing="10">
            <tr>
                <td class="kpi-card">
                    <span class="label">Obras activas</span>
                    <span class="value">{{ number_format($kpis['active_projects']) }}</span>
                </td>
                <td class="kpi-card">
                    <span class="label">Total contratado</span>
                    <span class="value">{{ $currency($kpis['contracted_total']) }}</span>
                </td>
                <td class="kpi-card">
                    <span class="label">Total pagado</span>
                    <span class="value">{{ $currency($kpis['paid_total']) }}</span>
                </td>
                <td class="kpi-card">
                    <span class="label">Saldo pendiente</span>
                    <span class="value">{{ $currency($kpis['pending_balance']) }}</span>
                </td>
            </tr>
        </table>

        <table class="kpi-grid" cellspacing="10">
            <tr>
                <td class="kpi-card">
                    <span class="label">Pagos vencidos</span>
                    <span class="value">{{ number_format($kpis['overdue_payments']) }}</span>
                </td>
                <td class="kpi-card">
                    <span class="label">Contratos activos</span>
                    <span class="value">{{ number_format($kpis['active_contracts']) }}</span>
                </td>
                <td class="kpi-card">
                    <span class="label">Solicitudes pendientes</span>
                    <span class="value">{{ number_format($kpis['pending_requests']) }}</span>
                </td>
                <td class="kpi-card">
                    <span class="label">Documentos vencidos</span>
                    <span class="value">{{ number_format($kpis['expired_documents']) }}</span>
                </td>
            </tr>
        </table>

        <div class="section">
            <h2>Distribuci&oacute;n de obras por ciudad</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Ciudad</th>
                        <th>Total de obras</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($analytics['charts']['projects_by_city']['data']['labels'] ?? []) as $index => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ number_format($analytics['charts']['projects_by_city']['data']['datasets'][0]['data'][$index] ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="muted">Sin datos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Top proveedores por monto contratado</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Monto contratado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($analytics['charts']['top_suppliers']['data']['labels'] ?? []) as $index => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $currency((float) ($analytics['charts']['top_suppliers']['data']['datasets'][0]['data'][$index] ?? 0)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="muted">Sin contratos suficientes para calcular ranking.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Alertas ejecutivas</h2>
            <table class="two-col" cellspacing="10">
                <tr>
                    @forelse ($analytics['highlights'] as $highlight)
                        <td>
                            <div class="summary-note">
                                <span class="label">{{ $highlight['label'] }}</span>
                                <div style="font-size: 14px; font-weight: bold;">{{ $highlight['value'] }}</div>
                                <div class="muted" style="margin-top: 4px;">{{ $highlight['meta'] }}</div>
                            </div>
                        </td>
                        @if ($loop->iteration % 2 === 0)
                            </tr><tr>
                        @endif
                    @empty
                        <td>
                            <div class="summary-note muted">Todav&iacute;a no hay suficiente actividad para destacar alertas.</div>
                        </td>
                    @endforelse
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
