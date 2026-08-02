<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertSee(route('order.tracking'), false);
    }

    public function test_home_page_contains_cookie_consent_markup(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Cookie preferences')
            ->assertSee('cookie-consent__backdrop', false)
            ->assertSee('VoidForgeStore uses cookies to keep the site working correctly. Here is what each option means:')
            ->assertSee('Essential only')
            ->assertSee('Accept all');
    }

    public function test_cookie_consent_route_persists_the_selected_choice(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->post(route('cookie-consent.store'), [
            'consent' => 'all',
            'return_to' => '/products',
        ])
            ->assertRedirect('/products')
            ->assertSessionHas('voidforgestore_cookie_consent', 'all')
            ->assertCookie('voidforgestore_cookie_consent', 'all');
    }

}
