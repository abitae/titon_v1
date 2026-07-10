<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 14px 0 6px; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; font-size: 8px; }
        .muted { color: #64748b; font-size: 8px; }
        .summary { margin-top: 8px; padding: 7px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .grid td:first-child { width: 18%; color: #64748b; font-weight: bold; }
    </style>
</head>
<body>
<h1>Historial del equipo {{ $equipment->internal_code }}</h1>
<p class="muted">Generado {{ $generatedAt->format('d/m/Y H:i') }} por {{ $actor->name }}</p>

<div class="summary">
    <strong>{{ $equipment->name }}</strong> · {{ $equipment->typeLabel() }} · {{ $equipment->operational_status }}
    <br>
    Obra actual: {{ $equipment->workProject ? $equipment->workProject->code.' - '.$equipment->workProject->name : 'Sin obra asignada' }}
</div>

<h2>Datos generales</h2>
<table class="grid">
    <tbody>
    <tr><td>Marca / modelo</td><td>{{ trim(($equipment->brand ?? '').' '.($equipment->model ?? '')) ?: '-' }}</td><td>Serie / placa</td><td>{{ $equipment->serial_number ?: '-' }} / {{ $equipment->plate ?: '-' }}</td></tr>
    <tr><td>Kilometraje</td><td>{{ number_format((float) ($equipment->odometer_km ?? 0), 2) }}</td><td>Horometro</td><td>{{ number_format((float) ($equipment->hour_meter ?? 0), 2) }}</td></tr>
    <tr><td>Responsable</td><td>{{ $equipment->responsibleUser?->name ?? '-' }}</td><td>Ciudad</td><td>{{ $equipment->city ?: '-' }}</td></tr>
    <tr><td>Observaciones</td><td colspan="3">{{ $equipment->observations ?: '-' }}</td></tr>
    </tbody>
</table>

<h2>Revisiones tecnicas</h2>
<table>
    <thead><tr><th>Revision</th><th>Vencimiento</th><th>Resultado</th><th>Centro</th><th>Responsable</th><th>Estado</th></tr></thead>
    <tbody>
    @forelse ($equipment->technicalInspections as $inspection)
        <tr>
            <td>{{ $inspection->reviewed_at?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $inspection->due_at?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $inspection->result }}</td>
            <td>{{ $inspection->inspection_center ?? '-' }}</td>
            <td>{{ $inspection->responsibleUser?->name ?? '-' }}</td>
            <td>{{ $inspection->status }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Sin revisiones registradas.</td></tr>
    @endforelse
    </tbody>
</table>

<h2>Mantenimientos preventivos</h2>
<table>
    <thead><tr><th>Codigo</th><th>Tipo</th><th>Programado</th><th>Km</th><th>Hrs</th><th>Costo</th><th>Responsable</th><th>Estado</th></tr></thead>
    <tbody>
    @forelse ($equipment->preventiveMaintenances as $maintenance)
        <tr>
            <td>{{ $maintenance->code }}</td>
            <td>{{ $maintenance->maintenance_type }}</td>
            <td>{{ $maintenance->scheduled_date?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $maintenance->scheduled_odometer ?? '-' }}</td>
            <td>{{ $maintenance->scheduled_hour_meter ?? '-' }}</td>
            <td>S/ {{ number_format((float) ($maintenance->cost ?? 0), 2) }}</td>
            <td>{{ $maintenance->responsibleUser?->name ?? '-' }}</td>
            <td>{{ $maintenance->status }}</td>
        </tr>
    @empty
        <tr><td colspan="8">Sin preventivos registrados.</td></tr>
    @endforelse
    </tbody>
</table>

<h2>Mantenimientos correctivos</h2>
<table>
    <thead><tr><th>Codigo</th><th>Fecha falla</th><th>Falla</th><th>Diagnostico</th><th>Taller</th><th>Costo real</th><th>Estado</th></tr></thead>
    <tbody>
    @forelse ($equipment->correctiveMaintenances as $maintenance)
        <tr>
            <td>{{ $maintenance->code }}</td>
            <td>{{ $maintenance->failure_at?->format('d/m/Y H:i') ?? '-' }}</td>
            <td>{{ $maintenance->failure_description }}</td>
            <td>{{ $maintenance->diagnosis ?: '-' }}</td>
            <td>{{ $maintenance->supplier_workshop ?: '-' }}</td>
            <td>S/ {{ number_format((float) ($maintenance->real_cost ?? 0), 2) }}</td>
            <td>{{ $maintenance->status }}</td>
        </tr>
    @empty
        <tr><td colspan="7">Sin correctivos registrados.</td></tr>
    @endforelse
    </tbody>
</table>

<h2>Ordenes de trabajo</h2>
<table>
    <thead><tr><th>OT</th><th>Tipo</th><th>Emision</th><th>Programado</th><th>Trabajo</th><th>Responsable</th><th>Costo</th><th>Estado</th></tr></thead>
    <tbody>
    @forelse ($equipment->workOrders as $workOrder)
        <tr>
            <td>{{ $workOrder->code }}</td>
            <td>{{ $workOrder->type }}</td>
            <td>{{ $workOrder->issued_at?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $workOrder->scheduled_date?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $workOrder->work_description ?: '-' }}</td>
            <td>{{ $workOrder->responsibleUser?->name ?? '-' }}</td>
            <td>S/ {{ number_format((float) ($workOrder->total_cost ?? 0), 2) }}</td>
            <td>{{ $workOrder->status }}</td>
        </tr>
    @empty
        <tr><td colspan="8">Sin ordenes de trabajo registradas.</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
