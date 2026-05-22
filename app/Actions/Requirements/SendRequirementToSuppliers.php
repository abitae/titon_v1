<?php

namespace App\Actions\Requirements;

use App\Enums\InvitationStatus;
use App\Enums\RequirementStatus;
use App\Models\Requirement;
use App\Models\RequirementSupplierInvitation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SendRequirementToSuppliers
{
    /**
     * @param  list<int>  $supplierIds
     */
    public function handle(
        Requirement $requirement,
        array $supplierIds,
        User $sender,
        ?string $message = null,
        ?string $responseDeadline = null,
    ): Requirement {
        abort_unless(in_array($requirement->status, [
            RequirementStatus::Created->value(),
            RequirementStatus::InProcess->value(),
        ], true), 422);

        DB::transaction(function () use ($requirement, $supplierIds, $sender, $message, $responseDeadline): void {
            foreach ($supplierIds as $supplierId) {
                $supplier = Supplier::query()
                    ->where('company_id', $requirement->company_id)
                    ->findOrFail($supplierId);

                RequirementSupplierInvitation::query()->updateOrCreate(
                    [
                        'company_id' => $requirement->company_id,
                        'requirement_id' => $requirement->id,
                        'supplier_id' => $supplier->id,
                    ],
                    [
                        'sent_by' => $sender->id,
                        'sent_at' => now(),
                        'status' => InvitationStatus::Sent->value(),
                        'message' => $message,
                        'response_deadline' => $responseDeadline,
                    ],
                );
            }

            $requirement->update(['status' => RequirementStatus::InProcess->value()]);
        });

        return $requirement->refresh();
    }
}
