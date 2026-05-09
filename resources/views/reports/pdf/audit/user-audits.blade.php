<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Auditoria de usuarios</title>
    @include('reports.pdf.partials.styles')
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>Auditoria de usuarios</h1>
            <p>Reporte generado el {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="section">
            <h2>Filtros aplicados</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Usuario</th>
                        <th>Modulo</th>
                        <th>Accion</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $filters['empresa'] }}</td>
                        <td>{{ $filters['usuario'] }}</td>
                        <td>{{ $filters['modulo'] }}</td>
                        <td>{{ $filters['accion'] }}</td>
                        <td>{{ $filters['desde'] }}</td>
                        <td>{{ $filters['hasta'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Eventos registrados</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Empresa</th>
                        <th>Usuario</th>
                        <th>Modulo</th>
                        <th>Accion</th>
                        <th>Registro</th>
                        <th>IP / Dispositivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($audits as $audit)
                        <tr>
                            <td>{{ $audit->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $audit->company?->name ?? 'Sin empresa' }}</td>
                            <td>{{ $audit->user_name ?: 'Sistema' }}</td>
                            <td>{{ $audit->module ?: 'Sistema' }}</td>
                            <td>{{ str($audit->action ?: $audit->event)->replace('_', ' ')->title() }}</td>
                            <td>{{ class_basename((string) $audit->auditable_type) }} #{{ $audit->auditable_id ?: '-' }}</td>
                            <td>{{ $audit->ip_address ?: 'Sin IP' }} / {{ $audit->device ?: 'Sin dispositivo' }}</td>
                        </tr>
                        <tr>
                            <td colspan="7">
                                <strong>Antes:</strong> {{ json_encode($audit->old_values ?? [], JSON_UNESCAPED_UNICODE) }}<br>
                                <strong>Despues:</strong> {{ json_encode($audit->new_values ?? [], JSON_UNESCAPED_UNICODE) }}
                                @if ($audit->observation)
                                    <br><strong>Observacion:</strong> {{ $audit->observation }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No hay registros para exportar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
