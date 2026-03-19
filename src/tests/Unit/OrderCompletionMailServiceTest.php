<?php

namespace Tests\Unit;

use App\Mail\OrderCompletedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Category;
use App\Services\OrderCompletionMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderCompletionMailServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_the_completed_order_email_once_with_a_pdf_attachment(): void
    {
        Mail::fake();

        $order = $this->paidOrder();

        $service = app(OrderCompletionMailService::class);

        $service->sendFor($order);
        $service->sendFor($order);

        Mail::assertSent(OrderCompletedMail::class, function (OrderCompletedMail $mail) use ($order): bool {
            return $mail->order->is($order)
                && count($mail->attachments()) === 1;
        });

        Mail::assertSentCount(1);

        $this->assertNotNull($order->fresh()->receipt_emailed_at);
    }

    private function paidOrder(): Order
    {
        $category = Category::query()->create([
            'name' => 'Shirts',
            'slug' => 'shirts',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Null Crest Tee',
            'slug' => 'null-crest-tee',
            'sku' => 'VF-TEE-100',
            'description' => 'Void shirt',
            'price_cents' => 2800,
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'status' => 'paid',
            'currency' => 'EUR',
            'subtotal_cents' => 2800,
            'shipping_cents' => 500,
            'total_cents' => 3300,
            'customer_name' => 'Cookie',
            'customer_email' => 'cookie@example.test',
            'customer_phone' => '+359-88-000-0000',
            'shipping_address_line_1' => '13 Void Circuit',
            'shipping_address_line_2' => null,
            'shipping_city' => 'Sofia',
            'shipping_state' => 'Sofia City',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'BG',
            'placed_at' => now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_size' => 'M',
            'unit_price_cents' => 2800,
            'quantity' => 1,
            'line_total_cents' => 2800,
        ]);

        Payment::query()->create([
            'provider' => 'stripe',
            'transaction_id' => 'pi_test_123',
            'amount' => 3300,
            'status' => 'paid',
            'order_id' => $order->id,
        ]);

        return $order->fresh(['items.product', 'payments']);
    }
}
