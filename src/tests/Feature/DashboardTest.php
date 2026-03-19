<?php

namespace Tests\Feature;

use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_basic_user_sees_my_account_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Account')
            ->assertSee('Cookie')
            ->assertSee('Saved shipping addresses')
            ->assertDontSee('Add Shipping Address');
    }

    public function test_user_can_open_saved_shipping_addresses_page_from_my_account(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
        ]);

        $this->actingAs($user)
            ->get(route('account.addresses.index'))
            ->assertOk()
            ->assertSee('Saved Shipping Addresses')
            ->assertSee('Add shipping address');
    }

    public function test_my_account_page_shows_the_default_shipping_address_when_available(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
        ]);

        ShippingAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Home',
            'recipient_name' => 'Cookie',
            'phone' => '+359-88-000-0000',
            'address_line_1' => '13 Void Circuit',
            'address_line_2' => null,
            'city' => 'Sofia',
            'state' => 'Sofia City',
            'postal_code' => '1000',
            'country' => 'BG',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Default shipping address')
            ->assertSee('Home')
            ->assertSee('13 Void Circuit');
    }

    public function test_user_can_add_a_shipping_address_from_my_account(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
        ]);

        $this->actingAs($user)
            ->post(route('account.addresses.store'), [
                'label' => 'Home',
                'recipient_name' => 'Cookie',
                'phone' => '+359-88-000-0000',
                'address_line_1' => '13 Void Circuit',
                'address_line_2' => 'Suite A',
                'city' => 'Sofia',
                'state' => 'Sofia City',
                'postal_code' => '1000',
                'country' => 'BG',
                'is_default' => 1,
            ])
            ->assertRedirect(route('account.addresses.index'));

        $this->assertDatabaseHas('shipping_addresses', [
            'user_id' => $user->id,
            'label' => 'Home',
            'recipient_name' => 'Cookie',
            'country' => 'BG',
            'is_default' => 1,
        ]);
    }

    public function test_user_can_update_a_saved_shipping_address(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
        ]);

        $address = ShippingAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Home',
            'recipient_name' => 'Cookie',
            'phone' => '+359-88-000-0000',
            'address_line_1' => '13 Void Circuit',
            'address_line_2' => null,
            'city' => 'Sofia',
            'state' => 'Sofia City',
            'postal_code' => '1000',
            'country' => 'BG',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->patch(route('account.addresses.update', $address), [
                'label' => 'Studio',
                'recipient_name' => 'Cookie Destroyer',
                'phone' => '+359-88-111-1111',
                'address_line_1' => '77 Shadow Lane',
                'address_line_2' => 'Floor 2',
                'city' => 'Plovdiv',
                'state' => 'Plovdiv',
                'postal_code' => '4000',
                'country' => 'BG',
                'is_default' => 1,
            ])
            ->assertRedirect(route('account.addresses.index'));

        $this->assertDatabaseHas('shipping_addresses', [
            'id' => $address->id,
            'label' => 'Studio',
            'recipient_name' => 'Cookie Destroyer',
            'address_line_1' => '77 Shadow Lane',
            'city' => 'Plovdiv',
        ]);
    }

    public function test_user_can_delete_a_saved_shipping_address(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
        ]);

        $address = ShippingAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Home',
            'recipient_name' => 'Cookie',
            'phone' => '+359-88-000-0000',
            'address_line_1' => '13 Void Circuit',
            'address_line_2' => null,
            'city' => 'Sofia',
            'state' => 'Sofia City',
            'postal_code' => '1000',
            'country' => 'BG',
            'is_default' => true,
        ]);

        $this->actingAs($user)
            ->delete(route('account.addresses.destroy', $address))
            ->assertRedirect(route('account.addresses.index'));

        $this->assertSoftDeleted('shipping_addresses', [
            'id' => $address->id,
        ]);

        $this->assertDatabaseHas('shipping_addresses', [
            'id' => $address->id,
            'label' => 'Deleted address',
            'recipient_name' => 'Removed recipient',
            'phone' => null,
            'address_line_1' => 'Redacted address',
            'city' => 'Redacted city',
            'postal_code' => '0000',
            'country' => 'BG',
            'is_default' => 0,
        ]);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
        ]);

        $this->actingAs($user)
            ->delete(route('account.destroy'))
            ->assertRedirect(route('products.index'));

        $this->assertGuest();
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }
}
