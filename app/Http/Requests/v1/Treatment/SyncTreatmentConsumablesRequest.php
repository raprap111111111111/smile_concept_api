<?php

namespace App\Http\Requests\v1\Treatment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The recipe is part of the treatment's definition, so editing it is gated on
 * `treatment.update` rather than an inventory permission — a dentist who may
 * change what an extraction costs may also say what it consumes.
 */
class SyncTreatmentConsumablesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('treatment.update');
    }

    public function rules(): array
    {
        return [
            'consumables' => ['present', 'array'],
            'consumables.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'consumables.*.quantity_per_use' => ['required', 'integer', 'min:1'],
            'consumables.*.is_optional' => ['nullable', 'boolean'],
            'consumables.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $lines = $this->input('consumables', []);

            if (! is_array($lines)) {
                return;
            }

            // The table has unique(treatment_id, item_id) — one line per item,
            // with quantity_per_use carrying the amount. Two lines for the same
            // item would otherwise fail at the database with a message nobody
            // can act on.
            $seen = [];

            foreach ($lines as $index => $line) {
                $itemId = $line['item_id'] ?? null;

                if ($itemId === null) {
                    continue;
                }

                if (isset($seen[$itemId])) {
                    $validator->errors()->add(
                        "consumables.{$index}.item_id",
                        'This item is already on the list — set its quantity there instead of adding a second line.',
                    );
                }

                $seen[$itemId] = true;
            }
        });
    }

    public function messages(): array
    {
        return [
            'consumables.present' => 'Send a consumables list, even if it is empty — an empty list clears the recipe.',
        ];
    }
}
