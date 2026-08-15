<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Carries the stock that already exists into the new batch/ledger model.
 *
 * Every `inventories` row becomes one opening-balance batch plus one ledger
 * movement, so `quantity` stays derivable as the sum of movement deltas from
 * the very first row. Without this, existing stock would read as zero the
 * moment the aggregate starts being recalculated from the ledger.
 *
 * Uses the query builder rather than models on purpose: a migration has to keep
 * working after the models move on.
 */
return new class extends Migration {
    /** Lets down() find exactly the rows this migration created. */
    private const MARKER = 'Opening balance carried over from pre-ledger stock.';

    public function up(): void
    {
        $now = now();

        DB::table('inventories')->chunkById(200, function ($rows) use ($now): void {
            foreach ($rows as $row) {
                $batchId = null;

                // A zero or negative row has nothing physical to put in a batch,
                // but still earns a movement so the ledger explains the balance.
                if ($row->quantity > 0) {
                    $batchId = DB::table('inventory_batches')->insertGetId([
                        'branch_id'          => $row->branch_id,
                        'item_id'            => $row->item_id,
                        'lot_number'         => null,
                        'expiry_date'        => $row->expiry_date,
                        'quantity_received'  => $row->quantity,
                        'quantity_remaining' => $row->quantity,
                        'received_at'        => $row->created_at
                            ? date('Y-m-d', strtotime((string) $row->created_at))
                            : $now->toDateString(),
                        'notes'              => self::MARKER,
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);
                }

                DB::table('stock_movements')->insert([
                    'branch_id'          => $row->branch_id,
                    'item_id'            => $row->item_id,
                    'inventory_batch_id' => $batchId,
                    'type'               => 'opening_balance',
                    'quantity_delta'     => $row->quantity,
                    'balance_after'      => $row->quantity,
                    'reason'             => self::MARKER,
                    'reference_type'     => null,
                    'reference_id'       => null,
                    'performed_by'       => null,
                    'notes'              => null,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Movements first — they hold the FK onto the batches.
        DB::table('stock_movements')->where('type', 'opening_balance')->delete();
        DB::table('inventory_batches')->where('notes', self::MARKER)->delete();
    }
};
