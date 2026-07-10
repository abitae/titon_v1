<?php

namespace App\Reports\Pdf;

use App\Models\User;
use App\Services\Pdf\MpdfBuilder;
use App\Services\Pdf\PdfBrandingResolver;

class PdfFormatPreviewReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
        protected PdfBrandingResolver $brandingResolver,
    ) {}

    public function build(User $user): string
    {
        return $this->mpdfBuilder->buildFromView(
            'reports.pdf.preview.sample',
            ['user' => $user],
            'Vista previa PDF',
            $user,
            $this->brandingResolver->resolveForPreview($user),
        );
    }
}
