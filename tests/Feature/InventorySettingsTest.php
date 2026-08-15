<?php

namespace Tests\Feature;

use App\Domain\Inventories\DTOs\InventorySettings;
use App\Domain\Settings\Services\SettingService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventorySettingsTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/v1/inventory-settings';

    private User $admin;
    private User $reader;

    /** A complete, valid body — every key is required, so a save is all-or-nothing. */
    private const VALID = [
        'inventory_auto_deduct_enabled'  => true,
        'inventory_allow_negative_stock' => false,
        'inventory_track_expiry'         => true,
        'inventory_expiry_warning_days'  => 45,
        'inventory_default_minimum_threshold' => 15,
        'inventory_low_stock_alert_enabled'   => true,
        'inventory_low_stock_alert_hour'      => 9,
        'inventory_low_stock_cooldown_days'   => 5,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'setting.view', 'guard_name' => 'api']);
        Permission::create(['name' => 'setting.update', 'guard_name' => 'api']);

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo(['setting.view', 'setting.update']);

        $readerRole = Role::create(['name' => 'assistant', 'guard_name' => 'api']);
        $readerRole->givePermissionTo('setting.view');

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->reader = User::factory()->create();
        $this->reader->assignRole($readerRole);
    }

    public function test_defaults_are_returned_when_nothing_is_stored(): void
    {
        Passport::actingAs($this->admin, ['*'], 'api');

        // No settings rows exist. A missing row must fall back to its default
        // rather than fataling — this endpoint sits behind the completion path.
        $this->getJson(self::URL)
            ->assertOk()
            ->assertJsonPath('data.inventory_auto_deduct_enabled', InventorySettings::DEFAULT_AUTO_DEDUCT)
            ->assertJsonPath('data.inventory_expiry_warning_days', InventorySettings::DEFAULT_EXPIRY_WARNING_DAYS)
            ->assertJsonPath('data.inventory_low_stock_alert_hour', InventorySettings::DEFAULT_LOW_STOCK_ALERT_HOUR);
    }

    public function test_saving_round_trips_through_storage(): void
    {
        Passport::actingAs($this->admin, ['*'], 'api');

        $this->putJson(self::URL, self::VALID)
            ->assertOk()
            ->assertJsonPath('data.inventory_expiry_warning_days', 45)
            ->assertJsonPath('data.inventory_allow_negative_stock', false)
            ->assertJsonPath('data.inventory_low_stock_alert_hour', 9);

        // The response is rebuilt from storage, so a fresh read must agree.
        $this->getJson(self::URL)
            ->assertOk()
            ->assertJsonPath('data.inventory_expiry_warning_days', 45)
            ->assertJsonPath('data.inventory_allow_negative_stock', false);
    }

    public function test_a_saved_value_reaches_the_typed_facade(): void
    {
        Passport::actingAs($this->admin, ['*'], 'api');

        $this->putJson(self::URL, [...self::VALID, 'inventory_auto_deduct_enabled' => false])
            ->assertOk();

        // What enforcement actually reads, not just what the endpoint echoed.
        $settings = InventorySettings::make(app(SettingService::class));

        $this->assertFalse($settings->autoDeductEnabled);
    }

    public function test_booleans_survive_arriving_as_strings(): void
    {
        Passport::actingAs($this->admin, ['*'], 'api');

        // Clients send "1"/"0", true/false, or 1/0 depending on the stack.
        $this->putJson(self::URL, [...self::VALID, 'inventory_track_expiry' => '0'])
            ->assertOk()
            ->assertJsonPath('data.inventory_track_expiry', false);
    }

    public function test_an_out_of_range_alert_hour_is_refused(): void
    {
        Passport::actingAs($this->admin, ['*'], 'api');

        // 24 would mean the digest silently never fires.
        $this->putJson(self::URL, [...self::VALID, 'inventory_low_stock_alert_hour' => 24])
            ->assertStatus(422)
            ->assertJsonValidationErrors('inventory_low_stock_alert_hour');
    }

    public function test_a_partial_body_is_refused(): void
    {
        Passport::actingAs($this->admin, ['*'], 'api');

        $this->putJson(self::URL, ['inventory_auto_deduct_enabled' => true])
            ->assertStatus(422)
            ->assertJsonValidationErrors('inventory_expiry_warning_days');
    }

    public function test_reading_needs_setting_view_and_writing_needs_setting_update(): void
    {
        Passport::actingAs($this->reader, ['*'], 'api');

        $this->getJson(self::URL)->assertOk();
        $this->putJson(self::URL, self::VALID)->assertForbidden();
    }
}
