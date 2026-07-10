@php
    /** @var \App\Services\Pdf\PdfBrandingData $branding */
@endphp
<style>
    .pdf-header-classic,
    .pdf-header-banner {
        font-family: DejaVu Sans, sans-serif;
        color: #0f172a;
        font-size: 10px;
    }

    .pdf-header-banner {
        background: {{ $branding->primaryColor }};
        color: #f8fafc;
        padding: 8px 10px;
        border-radius: 8px;
    }

    .pdf-header-banner .pdf-title {
        color: #f8fafc;
    }

    .pdf-header-banner .pdf-meta {
        color: #e2e8f0;
    }

    .pdf-header-classic .pdf-accent {
        color: {{ $branding->secondaryColor }};
    }

    .pdf-logo {
        max-height: 42px;
        max-width: 120px;
    }

    .pdf-title {
        font-size: 13px;
        font-weight: bold;
        margin: 0;
        line-height: 1.2;
    }

    .pdf-meta {
        color: #64748b;
        font-size: 9px;
        line-height: 1.35;
        margin: 2px 0 0;
    }

    .pdf-header-rule {
        border-bottom: 2px solid {{ $branding->secondaryColor }};
        margin-top: 6px;
    }
</style>

@if ($branding->headerLayout === \App\Enums\PdfHeaderLayout::Banner)
    <div class="pdf-header-banner">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                @if ($branding->showLogo && $branding->logoFilesystemPath)
                    <td width="130" style="vertical-align: middle;">
                        <img src="{{ $branding->logoFilesystemPath }}" class="pdf-logo" alt="Logo" />
                    </td>
                @endif
                <td style="vertical-align: middle;">
                    @if ($branding->showCompanyName || $branding->showBusinessName)
                        <p class="pdf-title">{{ $branding->displayTitle() }}</p>
                    @endif
                    @foreach ($branding->metaLines() as $line)
                        <p class="pdf-meta">{{ $line }}</p>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>
@else
    <div class="pdf-header-classic">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                @if ($branding->showLogo && $branding->logoFilesystemPath)
                    <td width="130" style="vertical-align: top;">
                        <img src="{{ $branding->logoFilesystemPath }}" class="pdf-logo" alt="Logo" />
                    </td>
                @endif
                <td style="vertical-align: top;">
                    @if ($branding->showCompanyName || $branding->showBusinessName)
                        <p class="pdf-title pdf-accent">{{ $branding->displayTitle() }}</p>
                    @endif
                    @foreach ($branding->metaLines() as $line)
                        <p class="pdf-meta">{{ $line }}</p>
                    @endforeach
                </td>
            </tr>
        </table>
        <div class="pdf-header-rule"></div>
    </div>
@endif
