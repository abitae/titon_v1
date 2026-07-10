@php
    /** @var \App\Services\Pdf\PdfBrandingData $branding */
    $borderStyle = $branding->showFooterBorder ? 'border-top:1px solid #cbd5e1;' : '';
@endphp
<div style="{{ $borderStyle }}color:#64748b;font-family:DejaVu Sans,sans-serif;font-size:{{ $branding->footerFontSize }}px;padding-top:5px;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="text-align:left;vertical-align:top;">
                @if (filled($branding->footerText))
                    {{ $branding->footerText }}
                @else
                    {{ $branding->displayTitle() }}
                @endif
                @if ($branding->showGeneratedAt)
                    · {{ now()->format('d/m/Y H:i') }}
                @endif
            </td>
            <td style="text-align:right;vertical-align:top;white-space:nowrap;">
                @if ($branding->showPageNumbers)
                    {{ $title }} | {PAGENO}
                @else
                    {{ $title }}
                @endif
            </td>
        </tr>
    </table>
</div>
