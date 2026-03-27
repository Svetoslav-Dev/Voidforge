<?php

namespace App\Services;

use App\Jobs\SendCartReminderEmail;
use App\Models\CartReminder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CartReminderService
{
    public function __construct(
        private readonly CartService $cart
    ) {
    }

    public function syncFor(?User $user): void
    {
        if (! $user) {
            return;
        }

        $items = $this->snapshotItems();

        if ($items->isEmpty()) {
            CartReminder::query()
                ->where('user_id', $user->id)
                ->delete();

            return;
        }

        CartReminder::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'items' => $items->values()->all(),
                'subtotal_cents' => $this->cart->subtotalCents(),
                'discount_code' => session('cart.discount_code'),
                'last_activity_at' => now(),
                'reminder_sent_at' => null,
            ]
        );
    }

    public function clearFor(?User $user): void
    {
        if (! $user) {
            return;
        }

        CartReminder::query()
            ->where('user_id', $user->id)
            ->delete();
    }

    public function queueDueReminders(): int
    {
        $cutoff = now()->subDays(2);

        $dueReminders = CartReminder::query()
            ->with('user')
            ->where('last_activity_at', '<=', $cutoff)
            ->where(function ($query) {
                $query->whereNull('reminder_sent_at')
                    ->orWhereColumn('reminder_sent_at', '<', 'last_activity_at');
            })
            ->get();

        foreach ($dueReminders as $reminder) {
            SendCartReminderEmail::dispatch($reminder->id);

            $reminder->forceFill([
                'reminder_sent_at' => now(),
            ])->save();
        }

        return $dueReminders->count();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function snapshotItems(): Collection
    {
        return $this->cart->items()->map(function (array $item): array {
            return [
                'product_id' => $item['product']->id,
                'product_name' => $item['product']->name,
                'product_slug' => $item['product']->slug,
                'product_image_url' => $item['product']->image_url,
                'size' => $item['size'],
                'quantity' => $item['quantity'],
                'line_total_cents' => $item['line_total_cents'],
            ];
        })->values();
    }
}
