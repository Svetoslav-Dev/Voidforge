<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->upsertUser([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'is_admin' => false,
            'password' => 'password',
        ]);

        $this->migrateLegacyCookieUser();

        $cookie = $this->upsertUser([
            'email' => 'demo-user@example.test',
        ], [
            'name' => 'Demo User',
            'is_admin' => false,
            'password' => 'DemoPass123!',
        ]);

        $cookieDestroyer = $this->upsertUser([
            'email' => 'demo-admin@example.test',
        ], [
            'name' => 'Demo Admin',
            'is_admin' => true,
            'password' => 'DemoPass123!',
        ]);

        $categories = [
            [
                'name' => 'Classic Tees',
                'slug' => 'classic-tees',
                'description' => 'Clean everyday staples with a sharp fit and simple graphics.',
            ],
            [
                'name' => 'Heavyweight',
                'slug' => 'heavyweight',
                'description' => 'Heavier cotton shirts with a structured silhouette.',
            ],
            [
                'name' => 'Limited Drops',
                'slug' => 'limited-drops',
                'description' => 'Short-run designs for featured seasonal releases.',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::query()->updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        $products = [
            [
                'category_slug' => 'classic-tees',
                'name' => 'Forge Mark Tee',
                'slug' => 'forge-mark-tee',
                'sku' => 'VF-TEE-001',
                'description' => 'A clean front-mark t-shirt built for the core everyday catalog.',
                'price_cents' => 2800,
                'stock' => 25,
                'is_active' => true,
            ],
            [
                'category_slug' => 'classic-tees',
                'name' => 'Null Crest Tee',
                'slug' => 'null-crest-tee',
                'sku' => 'VF-TEE-004',
                'description' => 'A sharp front crest print with a washed black base and cold blue detailing.',
                'price_cents' => 3000,
                'stock' => 19,
                'is_active' => true,
            ],
            [
                'category_slug' => 'classic-tees',
                'name' => 'Quiet Signal Tee',
                'slug' => 'quiet-signal-tee',
                'sku' => 'VF-TEE-005',
                'description' => 'A stripped-back staple with a narrow chest glyph and soft midweight cotton.',
                'price_cents' => 2900,
                'stock' => 21,
                'is_active' => true,
            ],
            [
                'category_slug' => 'heavyweight',
                'name' => 'Anvil Heavy Tee',
                'slug' => 'anvil-heavy-tee',
                'sku' => 'VF-TEE-002',
                'description' => 'A heavyweight cotton tee with a boxier fit and minimal chest print.',
                'price_cents' => 3600,
                'stock' => 14,
                'is_active' => true,
            ],
            [
                'category_slug' => 'heavyweight',
                'name' => 'Deep Stack Tee',
                'slug' => 'deep-stack-tee',
                'sku' => 'VF-TEE-006',
                'description' => 'A dense heavyweight shirt with dropped shoulders and tonal sleeve marks.',
                'price_cents' => 3800,
                'stock' => 12,
                'is_active' => true,
            ],
            [
                'category_slug' => 'heavyweight',
                'name' => 'Blackout Frame Tee',
                'slug' => 'blackout-frame-tee',
                'sku' => 'VF-TEE-007',
                'description' => 'A structured oversized cut with a back panel print built for colder palettes.',
                'price_cents' => 4100,
                'stock' => 10,
                'is_active' => true,
            ],
            [
                'category_slug' => 'limited-drops',
                'name' => 'Midnight Drop Tee',
                'slug' => 'midnight-drop-tee',
                'sku' => 'VF-TEE-003',
                'description' => 'A limited-run shirt with a darker palette and back graphic treatment.',
                'price_cents' => 3900,
                'stock' => 8,
                'is_active' => true,
            ],
            [
                'category_slug' => 'limited-drops',
                'name' => 'Event Horizon Tee',
                'slug' => 'event-horizon-tee',
                'sku' => 'VF-TEE-008',
                'description' => 'A limited release with a full back void-ring graphic and reflective ink accents.',
                'price_cents' => 4400,
                'stock' => 6,
                'is_active' => true,
            ],
            [
                'category_slug' => 'limited-drops',
                'name' => 'Ghost Relay Tee',
                'slug' => 'ghost-relay-tee',
                'sku' => 'VF-TEE-009',
                'description' => 'A short-run drop with spectral linework, faded charcoal dye, and oversized print.',
                'price_cents' => 4600,
                'stock' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::query()
                ->where('slug', $productData['category_slug'])
                ->firstOrFail();

            unset($productData['category_slug']);

            Product::query()->updateOrCreate(
                ['slug' => $productData['slug']],
                $productData + ['category_id' => $category->id]
            );
        }

        $this->seedPurchaseHistory($cookie, [
            [
                'placed_at' => Carbon::parse('2026-03-10 18:40:00'),
                'status' => 'paid',
                'currency' => 'EUR',
                'customer_name' => 'Demo User',
                'customer_email' => 'demo-user@example.test',
                'customer_phone' => '+1-555-0142',
                'shipping_address_line_1' => '42 Quiet Relay Ave',
                'shipping_address_line_2' => 'Unit 5',
                'shipping_city' => 'Nightfall',
                'shipping_state' => 'NY',
                'shipping_postal_code' => '10001',
                'shipping_country' => 'BG',
                'items' => [
                    ['slug' => 'forge-mark-tee', 'quantity' => 1],
                    ['slug' => 'deep-stack-tee', 'quantity' => 1],
                ],
                'payment' => [
                    'provider' => 'stripe',
                    'transaction_id' => 'cs_cookie_1001',
                    'status' => 'paid',
                ],
            ],
            [
                'placed_at' => null,
                'status' => 'awaiting_payment',
                'currency' => 'EUR',
                'customer_name' => 'Demo User',
                'customer_email' => 'demo-user@example.test',
                'customer_phone' => '+1-555-0142',
                'shipping_address_line_1' => '42 Quiet Relay Ave',
                'shipping_address_line_2' => 'Unit 5',
                'shipping_city' => 'Nightfall',
                'shipping_state' => 'NY',
                'shipping_postal_code' => '10001',
                'shipping_country' => 'BG',
                'items' => [
                    ['slug' => 'ghost-relay-tee', 'quantity' => 1],
                ],
                'payment' => [
                    'provider' => 'paypal',
                    'transaction_id' => 'pp_cookie_pending_2002',
                    'status' => 'pending',
                ],
            ],
        ]);

        $this->seedPurchaseHistory($cookieDestroyer, [
            [
                'placed_at' => Carbon::parse('2026-03-16 19:20:00'),
                'status' => 'paid',
                'currency' => 'EUR',
                'customer_name' => 'Demo Admin',
                'customer_email' => 'demo-admin@example.test',
                'customer_phone' => '+359-88-100-1000',
                'shipping_address_line_1' => '99 Void Gate',
                'shipping_address_line_2' => null,
                'shipping_city' => 'Sofia',
                'shipping_state' => 'Sofia City',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'BG',
                'items' => [
                    ['slug' => 'midnight-drop-tee', 'quantity' => 1],
                    ['slug' => 'null-crest-tee', 'quantity' => 1],
                ],
                'payment' => [
                    'provider' => 'stripe',
                    'transaction_id' => 'cs_cookiedestroyer_3001',
                    'status' => 'paid',
                ],
            ],
            [
                'placed_at' => null,
                'status' => 'awaiting_payment',
                'currency' => 'EUR',
                'customer_name' => 'Demo Admin',
                'customer_email' => 'demo-admin@example.test',
                'customer_phone' => '+359-88-100-1000',
                'shipping_address_line_1' => '99 Void Gate',
                'shipping_address_line_2' => null,
                'shipping_city' => 'Sofia',
                'shipping_state' => 'Sofia City',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'BG',
                'items' => [
                    ['slug' => 'blackout-frame-tee', 'quantity' => 1],
                ],
                'payment' => [
                    'provider' => 'paypal',
                    'transaction_id' => 'pp_cookiedestroyer_pending_3002',
                    'status' => 'pending',
                ],
            ],
        ]);

        $this->seedDefaultShippingAddress($cookie, [
            'label' => 'Home',
            'recipient_name' => 'Demo User',
            'phone' => '+359-88-000-0000',
            'address_line_1' => '42 Quiet Relay Ave',
            'address_line_2' => 'Unit 5',
            'city' => 'Nightfall',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'BG',
        ]);

        $this->seedDefaultShippingAddress($cookieDestroyer, [
            'label' => 'HQ',
            'recipient_name' => 'Demo Admin',
            'phone' => '+359-88-100-1000',
            'address_line_1' => '99 Void Gate',
            'address_line_2' => null,
            'city' => 'Sofia',
            'state' => 'Sofia City',
            'postal_code' => '1000',
            'country' => 'BG',
        ]);
    }

    /**
     * Seed a receipt history for a fixed demo user.
     *
     * @param  array<int, array<string, mixed>>  $receiptOrders
     */
    private function seedPurchaseHistory(User $user, array $receiptOrders): void
    {
        foreach ($receiptOrders as $receiptOrder) {
            $lineItems = collect($receiptOrder['items'])->map(function (array $lineItem): array {
                $product = Product::query()->where('slug', $lineItem['slug'])->firstOrFail();

                return [
                    'product' => $product,
                    'quantity' => $lineItem['quantity'],
                    'line_total_cents' => $product->price_cents * $lineItem['quantity'],
                ];
            });

            $subtotalCents = (int) $lineItems->sum('line_total_cents');

            $identity = [
                'user_id' => $user->id,
                'status' => $receiptOrder['status'],
                'customer_email' => $receiptOrder['customer_email'],
                'shipping_address_line_1' => $receiptOrder['shipping_address_line_1'],
            ];

            if ($receiptOrder['placed_at']) {
                $identity['placed_at'] = $receiptOrder['placed_at'];
            }

            $order = Order::query()->updateOrCreate($identity, [
                'status' => $receiptOrder['status'],
                'currency' => $receiptOrder['currency'],
                'subtotal_cents' => $subtotalCents,
                'shipping_cents' => 0,
                'total_cents' => $subtotalCents,
                'customer_name' => $receiptOrder['customer_name'],
                'customer_email' => $receiptOrder['customer_email'],
                'customer_phone' => $receiptOrder['customer_phone'],
                'shipping_address_line_1' => $receiptOrder['shipping_address_line_1'],
                'shipping_address_line_2' => $receiptOrder['shipping_address_line_2'],
                'shipping_city' => $receiptOrder['shipping_city'],
                'shipping_state' => $receiptOrder['shipping_state'],
                'shipping_postal_code' => $receiptOrder['shipping_postal_code'],
                'shipping_country' => $receiptOrder['shipping_country'],
                'placed_at' => $receiptOrder['placed_at'],
            ]);

            foreach ($lineItems as $lineItem) {
                OrderItem::query()->updateOrCreate([
                    'order_id' => $order->id,
                    'product_sku' => $lineItem['product']->sku,
                ], [
                    'product_id' => $lineItem['product']->id,
                    'product_name' => $lineItem['product']->name,
                    'unit_price_cents' => $lineItem['product']->price_cents,
                    'quantity' => $lineItem['quantity'],
                    'line_total_cents' => $lineItem['line_total_cents'],
                ]);
            }

            Payment::query()->updateOrCreate([
                'provider' => $receiptOrder['payment']['provider'],
                'transaction_id' => $receiptOrder['payment']['transaction_id'],
            ], [
                'order_id' => $order->id,
                'amount' => $subtotalCents,
                'status' => $receiptOrder['payment']['status'],
            ]);
        }
    }

    /**
     * Seed a deterministic default shipping address for a fixed demo user.
     *
     * @param  array<string, mixed>  $address
     */
    private function seedDefaultShippingAddress(User $user, array $address): void
    {
        ShippingAddress::withTrashed()
            ->where('user_id', $user->id)
            ->update(['is_default' => false]);

        $shippingAddress = ShippingAddress::withTrashed()->updateOrCreate(
            [
                'user_id' => $user->id,
                'label' => $address['label'],
            ],
            $address + [
                'user_id' => $user->id,
                'is_default' => true,
            ]
        );

        if ($shippingAddress->trashed()) {
            $shippingAddress->restore();
        }
    }

    /**
     * Create or restore a fixed user record used by the local seed data.
     *
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $values
     */
    private function upsertUser(array $identity, array $values): User
    {
        $user = User::withTrashed()->updateOrCreate($identity, $values);

        if ($user->trashed()) {
            $user->restore();
        }

        return $user;
    }

    /**
     * Move the legacy demo seed user to the current gmail address.
     */
    private function migrateLegacyCookieUser(): void
    {
        $legacyUser = User::withTrashed()
            ->where('email', 'cookie@example.com')
            ->first();

        if (! $legacyUser) {
            return;
        }

        $currentUser = User::withTrashed()
            ->where('email', 'demo-user@example.test')
            ->first();

        if ($currentUser && ! $currentUser->is($legacyUser)) {
            Order::query()
                ->where('user_id', $legacyUser->id)
                ->update(['user_id' => $currentUser->id]);

            $legacyUser->email = 'cookie-legacy-'.$legacyUser->id.'@invalid.local';
            $legacyUser->save();
            $legacyUser->delete();

            return;
        }

        $legacyUser->email = 'demo-user@example.test';
        $legacyUser->save();

        if ($legacyUser->trashed()) {
            $legacyUser->restore();
        }
    }
}
