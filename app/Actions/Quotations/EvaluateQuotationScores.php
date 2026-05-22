<?php

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\QuotationScore;
use App\Models\QuotationScoreParameter;
use App\Models\SupplierQuotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EvaluateQuotationScores
{
    /**
     * @param  array<int, array{parameter_id: int, score: float, observation?: string|null}>  $scores
     */
    public function handle(SupplierQuotation $quotation, array $scores, User $evaluator): SupplierQuotation
    {
        return DB::transaction(function () use ($quotation, $scores, $evaluator): SupplierQuotation {
            $total = 0.0;

            foreach ($scores as $row) {
                $parameter = QuotationScoreParameter::query()
                    ->where('company_id', $quotation->company_id)
                    ->where('active', true)
                    ->findOrFail($row['parameter_id']);

                $score = min((float) $row['score'], (float) $parameter->max_score);
                $weighted = round($score * ((float) $parameter->weight / 100), 2);

                QuotationScore::query()->updateOrCreate(
                    [
                        'supplier_quotation_id' => $quotation->id,
                        'quotation_score_parameter_id' => $parameter->id,
                    ],
                    [
                        'score' => $score,
                        'weighted_score' => $weighted,
                        'evaluated_by' => $evaluator->id,
                        'observation' => $row['observation'] ?? null,
                    ],
                );

                $total += $weighted;
            }

            $quotation->update([
                'total_score' => round($total, 2),
                'status' => QuotationStatus::UnderEvaluation->value(),
            ]);

            return $quotation->refresh();
        });
    }
}
