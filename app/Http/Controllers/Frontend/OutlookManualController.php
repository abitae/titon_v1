<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OutlookManualController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        $manualPath = public_path(config('frontend.outlook_manual_path'));

        abort_unless(is_file($manualPath), Response::HTTP_NOT_FOUND);

        $filename = basename($manualPath);

        return response()->file($manualPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
