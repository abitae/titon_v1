<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Vista previa PDF</title>
    @include('reports.pdf.partials.styles')
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>Vista previa de formato PDF</h1>
            <p>Documento de ejemplo para {{ $user->name }} · {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="section">
            <h2>Encabezado corporativo</h2>
            <p class="muted">
                El logotipo y los datos de la empresa seleccionada se muestran en el encabezado de todas las exportaciones PDF
                (compras, mecanica, dashboard, auditoria y contratos).
            </p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Elemento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Logotipo de empresa</td>
                    <td>{{ $pdfBranding->showLogo ? 'Visible' : 'Oculto' }} · {{ $pdfBranding->logoWidth }} x {{ $pdfBranding->logoHeight }} mm · {{ $pdfBranding->logoPosition === 'right' ? 'Derecha' : 'Izquierda' }}</td>
                </tr>
                <tr>
                    <td>Layout de encabezado</td>
                    <td>{{ $pdfBranding->headerLayout->label() }}</td>
                </tr>
                <tr>
                    <td>Color primario</td>
                    <td>{{ $pdfBranding->primaryColor }}</td>
                </tr>
                <tr>
                    <td>Color secundario</td>
                    <td>{{ $pdfBranding->secondaryColor }}</td>
                </tr>
            </tbody>
        </table>

        <p class="summary-note" style="margin-top:16px;">
            Configure margenes, colores y datos visibles en Configuracion → Formatos PDF.
            El logo se toma de la ficha de la empresa seleccionada.
        </p>
    </div>
</body>
</html>
