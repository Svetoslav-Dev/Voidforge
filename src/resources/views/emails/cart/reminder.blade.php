<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>VoidForgeStore cart reminder</title>
    </head>
    <body style="margin: 0; padding: 24px; background: #050816; color: #edf3ff; font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;">
        <div style="max-width: 720px; margin: 0 auto; background: #0c1326; border: 1px solid rgba(136, 156, 211, 0.22); border-radius: 20px; padding: 24px;">
            <p style="margin: 0 0 8px; color: #8e99b8;">VoidForgeStore</p>
            <h1 style="margin: 0 0 12px; font-size: 28px;">Your cart is still waiting</h1>
            <p style="margin: 0 0 24px; color: #8e99b8; line-height: 1.6;">
                You left these shirts in your cart two days ago. If you still want them, you can come back and complete the order.
            </p>

            <div style="margin-bottom: 24px; padding: 18px; background: #121b34; border-radius: 16px;">
                <h2 style="margin: 0 0 12px; font-size: 18px;">Shirts</h2>
                @foreach ($cartReminder->items as $item)
                    <div style="display: flex; gap: 16px; align-items: center; padding: 14px 0; border-top: 1px solid rgba(136, 156, 211, 0.16);">
                        <img
                            src="{{ $item['product_image_url'] ?: asset('images/items/fallback.svg') }}"
                            alt="{{ $item['product_name'] }}"
                            width="96"
                            height="96"
                            style="display: block; width: 96px; height: 96px; border-radius: 14px; object-fit: cover; background: #081022;"
                        >
                        <div style="flex: 1;">
                            <p style="margin: 0 0 4px; font-size: 16px; font-weight: 700;">{{ $item['product_name'] }}</p>
                            <p style="margin: 0 0 4px; color: #8e99b8;">Size {{ $item['size'] }} · Quantity {{ $item['quantity'] }}</p>
                            <p style="margin: 0; color: #edf3ff;">{{ number_format($item['line_total_cents'] / 100, 2) }} EUR</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="padding: 18px; background: #121b34; border-radius: 16px;">
                @if ($cartReminder->discount_code)
                    <p style="margin: 0 0 6px;"><strong>Discount code:</strong> {{ $cartReminder->discount_code }}</p>
                @endif
                <p style="margin: 0;"><strong>Cart subtotal:</strong> {{ number_format($cartReminder->subtotal_cents / 100, 2) }} EUR</p>
            </div>
        </div>
    </body>
</html>
