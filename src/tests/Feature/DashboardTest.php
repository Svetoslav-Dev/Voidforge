<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_user_sees_my_account_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Cookie',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Account')
            ->assertSee('Cookie');
    }
}
