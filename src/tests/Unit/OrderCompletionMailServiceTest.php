<?php

namespace Tests\Unit;

use App\Jobs\SendOrderCompletedEmail;
use App\Mail\OrderCompletedMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\OrderCompletionMailService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderCompletionMailServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_queues_the_completed_order_email_once_and_marks_it_pending(): void
    {
        Queue::fake();

        $order = $this->paidOrder();
        $service = app(OrderCompletionMailService::class);

        $service->queueFor($order);
        $service->queueFor($order);

        Queue::assertPushed(SendOrderCompletedEmail::class, function (SendOrderCompletedEmail $job) use ($order): bool {
            return $job->orderId === $order->id;
        });
        Queue::assertPushed(SendOrderCompletedEmail::class, 1);

        $order->refresh();

        $this->assertSame(Order::EMAIL_STATUS_PENDING, $order->email_status);
        $this->assertNull($order->receipt_emailed_at);
    }

    public function test_it_delivers_the_queued_completed_order_email_and_marks_it_sent(): void
    {
        Mail::fake();

        $order = $this->paidOrder([
            'email_status' => Order::EMAIL_STATUS_PENDING,
        ]);

        $service = app(OrderCompletionMailService::class);

        $service->deliver($order);

        Mail::assertSent(OrderCompletedMail::class, function (OrderCompletedMail $mail) use ($order): bool {
            return $mail->order->is($order)
                && count($mail->attachments()) === 1;
        });

        $order->refresh();

        $this->assertSame(Order::EMAIL_STATUS_SENT, $order->email_status);
        $this->assertNotNull($order->receipt_emailed_at);
    }

    public function test_the_retry_command_requeues_failed_and_pending_order_emails(): void
    {
        Queue::fake();

        $pendingOrder = $this->paidOrder([
            'email_status' => Order::EMAIL_STATUS_PENDING,
        ]);
        $failedOrder = $this->paidOrder([
            'email_status' => Order::EMAIL_STATUS_FAILED,
            'customer_email' => 'failed@example.test',
        ]);
        $this->paidOrder([
            'email_status' => Order::EMAIL_STATUS_SENT,
            'receipt_emailed_at' => now(),
            'customer_email' => 'sent@example.test',
        ]);

        $this->artisan('orders:retry-completed-emails')
            ->expectsOutput('Queued 2 order completion email retries.')
            ->assertExitCode(0);

        Queue::assertPushed(SendOrderCompletedEmail::class, function (SendOrderCompletedEmail $job) use ($pendingOrder): bool {
            return $job->orderId === $pendingOrder->id;
        });
        Queue::assertPushed(SendOrderCompletedEmail::class, function (SendOrderCompletedEmail $job) use ($failedOrder): bool {
            return $job->orderId === $failedOrder->id;
        });
        Queue::assertPushed(SendOrderCompletedEmail::class, 2);
    }

    public function test_the_job_marks_the_order_failed_after_final_failure(): void
    {
        $order = $this->paidOrder([
            'email_status' => Order::EMAIL_STATUS_PENDING,
        ]);

        $job = new SendOrderCompletedEmail($order->id);
        $job->failed(new Exception('SMTP quota exceeded.'));

        $this->assertSame(Order::EMAIL_STATUS_FAILED, $order->fresh()->email_status);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function paidOrder(array $overrides = []): Order
    {
        $category = Category::query()->create([
            'name' => 'Shirts '.uniqid(),
            'slug' => 'shirts-'.uniqid(),
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Null Crest Tee '.uniqid(),
            'slug' => 'null-crest-tee-'.uniqid(),
            'sku' => 'VF-TEE-'.uniqid(),
            'description' => 'Void shirt',
            'price_cents' => 2800,
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = Order::query()->create(array_merge([
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
            'email_status' => null,
            'receipt_emailed_at' => null,
        ], $overrides));

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
            'transaction_id' => 'pi_test_'.uniqid(),
            'amount' => 3300,
            'status' => 'paid',
            'order_id' => $order->id,
        ]);

        return $order->fresh(['items.product', 'payments']);
    }
}
