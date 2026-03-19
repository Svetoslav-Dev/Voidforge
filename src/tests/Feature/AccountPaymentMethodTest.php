<?php

namespace Tests\Feature;

use App\Models\SavedPaymentMethod;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AccountPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_user_can_open_saved_cards_page(): void
    {
        $user = User::factory()->create();

        SavedPaymentMethod::query()->create([
            'user_id' => $user->id,
            'provider' => 'stripe',
            'provider_payment_method_id' => 'pm_123',
            'brand' => 'visa',
            'last4' => '4242',
            'exp_month' => 12,
            'exp_year' => 2030,
            'is_default' => true,
        ]);

        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('isConfigured')->once()->andReturn(true);
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->actingAs($user)
            ->get(route('account.payment-methods.index'))
            ->assertOk()
            ->assertSee('Saved Cards')
            ->assertSee('VISA ending in 4242');
    }

    public function test_successful_stripe_return_stores_the_saved_card(): void
    {
        $user = User::factory()->create();

        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('completePaymentMethodSetup')
            ->once()
            ->withArgs(fn (User $candidate, string $sessionId) => $candidate->is($user) && $sessionId === 'cs_test_123');
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->actingAs($user)
            ->get(route('account.payment-methods.index', [
                'stripe' => 'success',
                'session_id' => 'cs_test_123',
            ]))
            ->assertRedirect(route('account.payment-methods.index'));
    }

    public function test_cancelled_stripe_return_redirects_to_a_clean_saved_cards_url(): void
    {
        $user = User::factory()->create();

        $stripe = Mockery::mock(StripePaymentService::class);
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->actingAs($user)
            ->get(route('account.payment-methods.index', [
                'stripe' => 'cancelled',
            ]))
            ->assertRedirect(route('account.payment-methods.index'));
    }

    public function test_user_can_start_adding_a_card_with_stripe(): void
    {
        $user = User::factory()->create();

        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('isConfigured')->once()->andReturn(true);
        $stripe->shouldReceive('startPaymentMethodSetup')->once()->withArgs(fn (User $candidate) => $candidate->is($user))->andReturn('https://checkout.stripe.test/setup/123');
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->actingAs($user)
            ->post(route('account.payment-methods.store'))
            ->assertRedirect('https://checkout.stripe.test/setup/123');
    }

    public function test_user_sees_an_error_when_stripe_is_not_configured_for_saved_cards(): void
    {
        $user = User::factory()->create();

        $stripe = Mockery::mock(StripePaymentService::class);
        $stripe->shouldReceive('isConfigured')->once()->andReturn(false);
        $this->app->instance(StripePaymentService::class, $stripe);

        $this->actingAs($user)
            ->post(route('account.payment-methods.store'))
            ->assertRedirect(route('account.payment-methods.index'))
            ->assertSessionHasErrors('payment_method');
    }

    public function test_user_can_change_the_default_saved_card(): void
    {
        $user = User::factory()->create();

        $firstCard = SavedPaymentMethod::query()->create([
            'user_id' => $user->id,
            'provider' => 'stripe',
            'provider_payment_method_id' => 'pm_first',
            'brand' => 'visa',
            'last4' => '4242',
            'exp_month' => 12,
            'exp_year' => 2030,
            'is_default' => true,
        ]);

        $secondCard = SavedPaymentMethod::query()->create([
            'user_id' => $user->id,
            'provider' => 'stripe',
            'provider_payment_method_id' => 'pm_second',
            'brand' => 'mastercard',
            'last4' => '4444',
            'exp_month' => 10,
            'exp_year' => 2031,
            'is_default' => false,
        ]);

        $this->actingAs($user)
            ->patch(route('account.payment-methods.default', $secondCard))
            ->assertRedirect(route('account.payment-methods.index'));

        $this->assertDatabaseHas('saved_payment_methods', [
            'id' => $firstCard->id,
            'is_default' => 0,
        ]);

        $this->assertDatabaseHas('saved_payment_methods', [
            'id' => $secondCard->id,
            'is_default' => 1,
        ]);
    }

    public function test_user_can_delete_a_saved_card(): void
    {
        $user = User::factory()->create();

        $firstCard = SavedPaymentMethod::query()->create([
            'user_id' => $user->id,
            'provider' => 'stripe',
            'provider_payment_method_id' => 'pm_first',
            'brand' => 'visa',
            'last4' => '4242',
            'exp_month' => 12,
            'exp_year' => 2030,
            'is_default' => true,
        ]);

        $secondCard = SavedPaymentMethod::query()->create([
            'user_id' => $user->id,
            'provider' => 'stripe',
            'provider_payment_method_id' => 'pm_second',
            'brand' => 'mastercard',
            'last4' => '4444',
            'exp_month' => 10,
            'exp_year' => 2031,
            'is_default' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('account.payment-methods.destroy', $firstCard))
            ->assertRedirect(route('account.payment-methods.index'));

        $this->assertDatabaseMissing('saved_payment_methods', [
            'id' => $firstCard->id,
        ]);

        $this->assertDatabaseHas('saved_payment_methods', [
            'id' => $secondCard->id,
            'is_default' => 1,
        ]);
    }
}
