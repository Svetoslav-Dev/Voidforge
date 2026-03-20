<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_non_admin_users_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.panel'))
            ->assertForbidden();
    }

    public function test_admin_can_view_the_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        Order::query()->create([
            'status' => 'paid',
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => now()->startOfYear()->addMonths(1),
        ]);

        Order::query()->create([
            'status' => 'paid',
            'currency' => 'EUR',
            'subtotal_cents' => 1900,
            'shipping_cents' => 0,
            'total_cents' => 1900,
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => now()->startOfYear()->subYear()->addMonths(1),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.panel'))
            ->assertOk()
            ->assertSee('Rule the void storefront')
            ->assertSee('Catalogs')
            ->assertSee('Revenue this year')
            ->assertSee((string) now()->year)
            ->assertSee((string) now()->subYear()->year)
            ->assertSee('Feb');
    }

    public function test_admin_can_open_my_account_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Account');
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Admin Item',
                'slug' => 'admin-item',
                'sku' => 'VF-ADMIN-001',
                'description' => 'Created from the admin panel.',
                'price' => 32.00,
                'stock' => 11,
                'is_active' => 1,
            ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'slug' => 'admin-item',
            'sku' => 'VF-ADMIN-001',
        ]);
    }

    public function test_admin_can_create_a_discount_code(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.discount-codes.store'), [
                'code' => 'VOID10',
                'description' => 'Ten percent off.',
                'type' => 'percent',
                'amount' => 10,
                'is_active' => 1,
            ])->assertRedirect();

        $this->assertDatabaseHas('discount_codes', [
            'code' => 'VOID10',
            'type' => 'percent',
            'amount' => 10,
        ]);
    }

    public function test_admin_can_update_a_discount_code(): void
    {
        $admin = User::factory()->admin()->create();
        $discountCode = DiscountCode::query()->create([
            'code' => 'VOID5',
            'description' => 'Five off',
            'type' => 'fixed',
            'amount' => 500,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.discount-codes.update', $discountCode), [
                'code' => 'VOID15',
                'description' => 'Fifteen percent off.',
                'type' => 'percent',
                'amount' => 15,
                'is_active' => 1,
            ])->assertRedirect();

        $this->assertDatabaseHas('discount_codes', [
            'id' => $discountCode->id,
            'code' => 'VOID15',
            'type' => 'percent',
            'amount' => 15,
        ]);
    }

    public function test_admin_can_update_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'existing-item',
            'sku' => 'VF-EXIST-001',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.products.update', $product), [
                'category_id' => $category->id,
                'name' => 'Updated Item',
                'slug' => 'updated-item',
                'sku' => 'VF-EXIST-001',
                'description' => 'Updated from admin.',
                'price' => 35.00,
                'stock' => 17,
                'is_active' => 0,
            ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'slug' => 'updated-item',
            'price_cents' => 3500,
            'is_active' => 0,
        ]);
    }

    public function test_admin_can_disable_sizes_for_a_shirt_and_the_product_page_reflects_it(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'midnight-drop-tee',
            'sku' => 'VF-MIDNIGHT-001',
            'stock' => 9,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.products.update', $product), [
                'category_id' => $category->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'description' => $product->description,
                'price' => number_format($product->price_cents / 100, 2, '.', ''),
                'stock' => $product->stock,
                'is_active' => 1,
                'disabled_sizes' => ['S', 'M'],
            ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('S · Sold out')
            ->assertSee('M · Sold out');
    }

    public function test_admin_can_upload_an_item_image_and_price_is_truncated_to_two_decimals(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $frontImage = UploadedFile::fake()->createWithContent(
            'admin-item.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9sX6lz8AAAAASUVORK5CYII=')
        );
        $backImage = UploadedFile::fake()->createWithContent(
            'admin-item-back.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9sX6lz8AAAAASUVORK5CYII=')
        );

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Image Item',
                'slug' => 'image-item',
                'sku' => 'VF-ADMIN-IMG',
                'description' => 'Created with uploaded artwork.',
                'price' => '28.009',
                'stock' => 9,
                'is_active' => 1,
                'image' => $frontImage,
                'back_image' => $backImage,
            ])->assertRedirect();

        $product = Product::query()->where('slug', 'image-item')->firstOrFail();

        $this->assertSame(2800, $product->price_cents);
        $this->assertNotNull($product->image_path);
        $this->assertNotNull($product->back_image_path);
        $this->assertFileExists(storage_path('app/public/'.$product->image_path));
        $this->assertFileExists(storage_path('app/public/'.$product->back_image_path));
    }

    public function test_admin_can_soft_delete_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_admin_can_restore_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        $product->delete();

        $this->actingAs($admin)
            ->patch(route('admin.products.restore', $product->slug))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_view_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::query()->create([
            'status' => 'pending',
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Forge Mark Tee',
            'product_sku' => 'VF-TEE-001',
            'product_size' => 'M',
            'unit_price_cents' => 2800,
            'quantity' => 1,
            'line_total_cents' => 2800,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Forge Mark Tee');
    }

    public function test_admin_can_archive_and_restore_an_order(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::query()->create([
            'status' => 'pending',
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 0,
            'total_cents' => 2800,
            'customer_name' => 'Taylor Forge',
            'customer_email' => 'taylor@example.test',
            'customer_phone' => '+1-555-0100',
            'shipping_address_line_1' => '123 Ember Street',
            'shipping_address_line_2' => 'Unit B',
            'shipping_city' => 'Ironvale',
            'shipping_state' => 'CA',
            'shipping_postal_code' => '90210',
            'shipping_country' => 'US',
            'placed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSoftDeleted('orders', [
            'id' => $order->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.restore', $order->id))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_view_users_and_soft_delete_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Archive Me',
            'email' => 'archive@example.test',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Archive Me');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_can_restore_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Restore Me',
            'email' => 'restore@example.test',
        ]);
        $user->delete();

        $this->actingAs($admin)
            ->patch(route('admin.users.restore', $user->id))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_users_listing_shows_last_login_date(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Recent Login',
            'email' => 'recent-login@example.test',
            'last_login_at' => now()->setDate(2026, 3, 19)->setTime(10, 30),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Recent Login')
            ->assertSee('Last login:')
            ->assertSee('Mar 19, 2026 10:30');
    }

    public function test_admin_can_create_a_user_and_choose_admin_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Admin',
                'email' => 'new-admin@example.test',
                'password' => 'secretpass123',
                'is_admin' => 1,
            ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-admin@example.test',
            'is_admin' => 1,
        ]);
    }

    public function test_admin_can_create_update_and_soft_delete_a_catalog(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Seasonal Drops',
                'slug' => 'seasonal-drops',
                'description' => 'Short-run seasonal catalog.',
            ])->assertRedirect();

        $category = Category::query()->where('slug', 'seasonal-drops')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.categories.update', $category), [
                'name' => 'Seasonal Capsules',
                'slug' => 'seasonal-capsules',
                'description' => 'Updated seasonal catalog.',
            ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'seasonal-capsules',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category->fresh()))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_can_restore_a_catalog(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create([
            'name' => 'Restore Catalog',
            'slug' => 'restore-catalog',
        ]);
        $category->delete();

        $this->actingAs($admin)
            ->patch(route('admin.categories.restore', $category->slug))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }
}
