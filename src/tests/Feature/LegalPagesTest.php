<?php

namespace Tests\Feature;

use App\Mail\LegalContactRequestMail;
use App\Mail\LegalContactRequestAcknowledgementMail;
use App\Models\LegalContactRequest;
use App\Models\Product;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_legal_pages_are_available(): void
    {
        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('Privacy Policy');

        $this->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('Terms and Conditions');

        $this->get(route('legal.returns'))
            ->assertOk()
            ->assertSee('Returns and Refunds');

        $this->get(route('legal.shipping'))
            ->assertOk()
            ->assertSee('Shipping and Delivery');

        $this->get(route('legal.cookies'))
            ->assertOk()
            ->assertSee('Cookie Policy');

        $this->get(route('legal.contact'))
            ->assertOk()
            ->assertSee('Contact and Trader Information');
    }

    public function test_footer_contains_links_to_the_main_legal_pages(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.returns'), false)
            ->assertSee(route('legal.shipping'), false)
            ->assertSee(route('legal.cookies'), false)
            ->assertSee(route('legal.contact'), false);
    }

    public function test_home_page_contains_cookie_consent_markup(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Cookie preferences')
            ->assertSee('Read the Cookie Policy')
            ->assertSee('Essential only')
            ->assertSee('Accept all');
    }

    public function test_contact_page_can_queue_a_support_request(): void
    {
        Mail::fake();

        $this->post(route('legal.contact.request'), [
            'name' => 'Svetoslav Forge',
            'email' => 'svetoslav@example.test',
            'topic' => 'privacy',
            'order_reference' => 'VF31',
            'message' => 'Please confirm what data is stored for my account.',
        ])->assertRedirect(route('legal.contact'));

        $this->assertDatabaseHas('legal_contact_requests', [
            'email' => 'svetoslav@example.test',
            'topic' => 'privacy',
            'order_reference' => 'VF31',
        ]);

        Mail::assertQueued(LegalContactRequestMail::class, function (LegalContactRequestMail $mail): bool {
            return $mail->payload['email'] === 'svetoslav@example.test'
                && $mail->payload['topic'] === 'privacy'
                && $mail->payload['order_reference'] === 'VF31';
        });

        Mail::assertQueued(LegalContactRequestAcknowledgementMail::class, function (LegalContactRequestAcknowledgementMail $mail): bool {
            return $mail->payload['email'] === 'svetoslav@example.test'
                && $mail->payload['topic'] === 'privacy'
                && $mail->payload['order_reference'] === 'VF31';
        });
    }

    public function test_shipping_step_contains_pre_payment_policy_links(): void
    {
        $product = Product::factory()->create([
            'stock' => 8,
            'price_cents' => 3200,
        ]);

        $this->withSession([
            'cart.items' => [$product->id.':M' => 1],
        ])->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Before you continue')
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.returns'), false)
            ->assertSee(route('legal.shipping'), false);
    }
}
