<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; font-size: 9px; }
        .muted { color: #64748b; font-size: 9px; margin-top: 4px; }
        .summary { margin: 8px 0; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 9px; }
    </style>
</head>
<body>
<h1>{{ $headingTitle }}</h1>
<p class="muted">Generado {{ $generatedAt->format('d/m/Y H:i') }} · {{ $actor->name }}</p>

@if ($summaryLines !== [])
    <div class="summary">
        @foreach ($summaryLines as $line)
            <div>{{ $line }}</div>
        @endforeach
    </div>
@endif

<table>
    <thead>
    <tr>
        @foreach ($headings as $h)
            <th>{{ $h }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($rows as $row)
        <tr>
            @foreach (array_values($row) as $cell)
                <td>{{ $cell }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
<p class="muted">Registros: {{ $rows->count() }}</p>
</body>
</html>
