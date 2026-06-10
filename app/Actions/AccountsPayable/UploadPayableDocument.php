<?php

namespace App\Actions\AccountsPayable;

use App\Enums\AccountsPayableStatus;
use App\Models\AccountsPayable;
use App\Models\PayableDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UploadPayableDocument
{
    public function handle(
        AccountsPayable $accountsPayable,
        PayableDocument $document,
        UploadedFile|TemporaryUploadedFile $file,
        User $uploader,
    ): PayableDocument {
        abort_unless(
            (int) $document->accounts_payable_id === (int) $accountsPayable->id,
            404,
        );

        $document->clearMediaCollection('archivo');

        $document
            ->addMedia($file->getRealPath())
            ->usingFileName($file->getClientOriginalName())
            ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
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
