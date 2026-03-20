<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/_test-error', function () {
            throw new RuntimeException('Debug details should not be shown here.');
        });
    }

    public function test_guest_users_see_generic_error_page_instead_of_debug_output(): void
    {
        $this->get('/_test-error')
            ->assertStatus(500)
            ->assertSee('Something went wrong')
            ->assertSee('An unexpected error occurred. Please try again.')
            ->assertDontSee('Debug details should not be shown here.');
    }

    public function test_non_admin_users_see_generic_error_page_instead_of_debug_output(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/_test-error')
            ->assertStatus(500)
            ->assertSee('Something went wrong')
            ->assertSee('An unexpected error occurred. Please try again.')
            ->assertDontSee('Debug details should not be shown here.');
    }

    public function test_admin_users_still_see_the_underlying_exception_output(): void
    {
        $admin = User::factory()->admin()->create();

        $this->withoutExceptionHandling();

        $this->actingAs($admin);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Debug details should not be shown here.');

        $this->get('/_test-error');
    }
}
