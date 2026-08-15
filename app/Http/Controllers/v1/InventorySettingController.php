<?php

namespace App\Http\Controllers\v1;

use App\Domain\Inventories\DTOs\InventorySettings;
use App\Domain\Settings\Services\SettingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\InventorySetting\GetInventorySettingsRequest;
use App\Http\Requests\v1\InventorySetting\UpdateInventorySettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * The whole inventory-settings form as one typed endpoint.
 *
 * Same shape as AppointmentSettingController: values live in the generic
 * `settings` table, GET reads them through the InventorySettings facade so it
 * shows exactly what enforcement uses (defaults included), and PUT writes via
 * bulkSet inside a transaction.
 */
class InventorySettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function show(GetInventorySettingsRequest $request): JsonResponse
    {
        return $this->successResponse(
            $this->toPayload(app(InventorySettings::class)),
            'Inventory settings retrieved.'
        );
    }

    public function update(UpdateInventorySettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Cast explicitly: booleans arrive as true/"1"/1 depending on the
        // client, and SettingService::inferType() keys off the PHP type when a
        // row does not exist yet.
        $payload = [
            'inventory_auto_deduct_enabled'  => filter_var($validated['inventory_auto_deduct_enabled'], FILTER_VALIDATE_BOOLEAN),
            'inventory_allow_negative_stock' => filter_var($validated['inventory_allow_negative_stock'], FILTER_VALIDATE_BOOLEAN),
            'inventory_track_expiry'         => filter_var($validated['inventory_track_expiry'], FILTER_VALIDATE_BOOLEAN),

            'inventory_expiry_warning_days'       => (int) $validated['inventory_expiry_warning_days'],
            'inventory_default_minimum_threshold' => (int) $validated['inventory_default_minimum_threshold'],

            'inventory_low_stock_alert_enabled' => filter_var($validated['inventory_low_stock_alert_enabled'], FILTER_VALIDATE_BOOLEAN),
            'inventory_low_stock_alert_hour'    => (int) $validated['inventory_low_stock_alert_hour'],
            'inventory_low_stock_cooldown_days' => (int) $validated['inventory_low_stock_cooldown_days'],
        ];

        DB::transaction(fn () => $this->settings->bulkSet($payload));

        // Rebuilt from storage rather than echoed from $payload: the response
        // has to prove the write, cache bust and re-read actually happened.
        $fresh = $this->toPayload(InventorySettings::make($this->settings));

        return $this->successResponse($fresh, 'Inventory settings updated.');
    }

    /**
     * Flat map keyed by settings-table keys — the same names the seeder,
     * documentation and any error message use.
     *
     * @return array<string, mixed>
     */
    private function toPayload(InventorySettings $s): array
    {
        return [
            'inventory_auto_deduct_enabled'  => $s->autoDeductEnabled,
            'inventory_allow_negative_stock' => $s->allowNegativeStock,
            'inventory_track_expiry'         => $s->trackExpiry,

            'inventory_expiry_warning_days'       => $s->expiryWarningDays,
            'inventory_default_minimum_threshold' => $s->defaultMinimumThreshold,

            'inventory_low_stock_alert_enabled' => $s->lowStockAlertsEnabled,
            'inventory_low_stock_alert_hour'    => $s->lowStockAlertHour,
            'inventory_low_stock_cooldown_days' => $s->lowStockCooldownDays,
        ];
    }
}
