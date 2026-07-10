@php
    /** @var \App\Services\Pdf\PdfBrandingData $branding */
    $baseHeaderStyle = 'font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 10px;';
    $logoStyle = "height: {$branding->logoHeight}mm; max-height: {$branding->logoHeight}mm; max-width: {$branding->logoWidth}mm; width: {$branding->logoWidth}mm;";
    $titleStyle = "font-size: {$branding->titleFontSize}px; font-weight: bold; margin: 0; line-height: 1.2;";
    $metaStyle = "font-size: {$branding->metaFontSize}px; line-height: 1.35; margin: 2px 0 0;";
@endphp

@if ($branding->headerLayout === \App\Enums\PdfHeaderLayout::Banner)
    <div style="{{ $baseHeaderStyle }} background: {{ $branding->primaryColor }}; color: #f8fafc; padding: {{ $branding->headerPadding }}px 10px; border-radius: 8px;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                @if ($branding->showLogo && $branding->logoFilesystemPath && $branding->logoPosition === 'left')
                    <td style="vertical-align: {{ $branding->logoVerticalAlignCss() }}; text-align: left; width: {{ $branding->logoCellWidth() }}mm;">
                        <img src="{{ $branding->logoFilesystemPath }}" style="{{ $logoStyle }}" alt="Logo" />
                    </td>
                @endif
                <td style="vertical-align: middle; text-align: {{ $branding->headerTextAlignCss() }};">
                    @if ($branding->showCompanyName || $branding->showBusinessName)
                        <p style="{{ $titleStyle }} color: #f8fafc;">{{ $branding->displayTitle() }}</p>
                    @endif
                    @foreach ($branding->metaLines() as $line)
                        <p style="{{ $metaStyle }} color: #e2e8f0;">{{ $line }}</p>
                    @endforeach
                </td>
                @if ($branding->showLogo && $branding->logoFilesystemPath && $branding->logoPosition === 'right')
                    <td style="vertical-align: {{ $branding->logoVerticalAlignCss() }}; text-align: right; width: {{ $branding->logoCellWidth() }}mm;">
                        <img src="{{ $branding->logoFilesystemPath }}" style="{{ $logoStyle }}" alt="Logo" />
                    </td>
                @endif
            </tr>
        </table>
    </div>
@else
    <div style="{{ $baseHeaderStyle }} padding-top: {{ max($branding->headerPadding - 6, 0) }}px;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                @if ($branding->showLogo && $branding->logoFilesystemPath && $branding->logoPosition === 'left')
                    <td style="vertical-align: {{ $branding->logoVerticalAlignCss() }}; text-align: left; width: {{ $branding->logoCellWidth() }}mm;">
                        <img src="{{ $branding->logoFilesystemPath }}" style="{{ $logoStyle }}" alt="Logo" />
                    </td>
                @endif
                <td style="vertical-align: top; text-align: {{ $branding->headerTextAlignCss() }};">
                    @if ($branding->showCompanyName || $branding->showBusinessName)
                        <p style="{{ $titleStyle }} color: {{ $branding->secondaryColor }};">{{ $branding->displayTitle() }}</p>
                    @endif
                    @foreach ($branding->metaLines() as $line)
                        <p style="{{ $metaStyle }} color: #64748b;">{{ $line }}</p>
                    @endforeach
                </td>
                @if ($branding->showLogo && $branding->logoFilesystemPath && $branding->logoPosition === 'right')
                    <td style="vertical-align: {{ $branding->logoVerticalAlignCss() }}; text-align: right; width: {{ $branding->logoCellWidth() }}mm;">
                        <img src="{{ $branding->logoFilesystemPath }}" style="{{ $logoStyle }}" alt="Logo" />
                    </td>
                @endif
            </tr>
        </table>
        @if ($branding->showHeaderRule)
            <div style="border-bottom: {{ $branding->headerRuleThickness }}px solid {{ $branding->secondaryColor }}; margin-top: 6px;"></div>
        @endif
    </div>
@endif
