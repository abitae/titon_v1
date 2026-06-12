<?php

namespace App\Actions\AccountsPayable;

use App\Enums\AccountsPayableStatus;
use App\Models\AccountsPayable;
use App\Models\PayableDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UploadPayableDocument
{
    public function handle(
        AccountsPayable $accountsPayable,
        PayableDocument $document,
        UploadedFile|TemporaryUploadedFile $file,
        User $uploader,
    ): PayableDocument {
        return DB::transaction(fn (): PayableDocument => $this->persistUpload($accountsPayable, $document, $file, $uploader));
    }

    protected function persistUpload(
        AccountsPayable $accountsPayable,
        PayableDocument $document,
        UploadedFile|TemporaryUploadedFile $file,
        User $uploader,
    ): PayableDocument {
        abort_unless(
            (int) $document->accounts_payable_id === (int) $accountsPayable->id,
            404,
        );

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $safeFileName = Str::uuid()->toString().'.'.$extension;

        $document->clearMediaCollection('archivo');

        $document
            ->addMedia($file->getRealPath())
            ->usingFileName($safeFileName)
            ->usingName(pathinfo($safeFileName, PATHINFO_FILENAME))
            ->toMediaCollection('archivo', 'public');

        abort_unless($document->refresh()->hasUploadedFile(), 422, 'No se pudo guardar el archivo del documento.');

        $document->update([
            'uploaded' => true,
            'uploaded_by' => $uploader->id,
            'uploaded_at' => now(),
            'status' => 'cargado',
        ]);

        $accountsPayable->refresh();

        if ($accountsPayable->requiredDocumentsUploaded()) {
            $accountsPayable->update([
                'status' => AccountsPayableStatus::ReadyForPayment->value(),
            ]);
        }

        return $document->refresh();
    }
}
