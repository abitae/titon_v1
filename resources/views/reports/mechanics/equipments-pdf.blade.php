<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; font-size: 10px; }
        .muted { color: #64748b; font-size: 10px; }
    </style>
</head>
<body>
<h1>Listado de equipos y maquinarias</h1>
<p class="muted">Empresa desde contexto de sesión · Generado {{ $generatedAt->format('d/m/Y H:i') }} por {{ $actor->name }}</p>
<table>
    <thead>
    <tr>
        <th>Codigo</th>
        <th>Nombre / tipo</th>
        <th>Obra</th>
        <th>Estado</th>
        <th>Km · Hrs</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($equipments as $row)
        <tr>
            <td>{{ $row->internal_code }}</td>
            <td>{{ $row->name }} · {{ $row->equipment_type }}</td>
            <td>{{ $row->workProject?->code }}</td>
            <td>{{ $row->operational_status }}</td>
            <td>{{ $row->odometer_km }} · {{ $row->hour_meter }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<p class="muted">Totales registros visibles por alcance empresa: {{ $equipments->count() }}</p>
</body>
</html>
