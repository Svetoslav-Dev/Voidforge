<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Voidforge order completed</title>
    </head>
    <body style="margin: 0; padding: 24px; background: #050816; color: #edf3ff; font-family: 'Trebuchet MS', 'Segoe UI', sans-serif;">
        <div style="max-width: 720px; margin: 0 auto; background: #0c1326; border: 1px solid rgba(136, 156, 211, 0.22); border-radius: 20px; padding: 24px;">
            <p style="margin: 0 0 8px; color: #8e99b8;">Voidforge</p>
            <h1 style="margin: 0 0 12px; font-size: 28px;">Order #VF{{ $order->id }} completed</h1>
            <p style="margin: 0 0 24px; color: #8e99b8; line-height: 1.6;">
                Your payment was confirmed. Attached is the PDF receipt for this purchase.
            </p>

            <div style="margin-bottom: 24px; padding: 18px; background: #121b34; border-radius: 16px;">
                <h2 style="margin: 0 0 12px; font-size: 18px;">Shipment details</h2>
                <p style="margin: 0 0 6px;"><strong>Name:</strong> {{ $order->customer_name }}</p>
                <p style="margin: 0 0 6px;"><strong>Email:</strong> {{ $order->customer_email }}</p>
                @if ($order->customer_phone)
                    <p style="margin: 0 0 6px;"><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                @endif
                <p style="margin: 0 0 6px;"><strong>Address:</strong> {{ $order->shipping_address_line_1 }}@if ($order->shipping_address_line_2), {{ $order->shipping_address_line_2 }}@endif</p>
                <p style="margin: 0 0 6px;"><strong>City:</strong> {{ $order->shipping_city }}</p>
                @if ($order->shipping_state)
                    <p style="margin: 0 0 6px;"><strong>State:</strong> {{ $order->shipping_state }}</p>
                @endif
                <p style="margin: 0 0 6px;"><strong>Postal code:</strong> {{ $order->shipping_postal_code }}</p>
                <p style="margin: 0;"><strong>Country:</strong> {{ $order->shipping_country }}</p>
            </div>

            <div style="margin-bottom: 24px; padding: 18px; background: #121b34; border-radius: 16px;">
                <h2 style="margin: 0 0 12px; font-size: 18px;">Shirts</h2>
                @foreach ($order->items as $item)
                    <div style="display: flex; gap: 16px; align-items: center; padding: 14px 0; border-top: 1px solid rgba(136, 156, 211, 0.16);">
                        <img
                            src="{{ $item->product?->image_url ?? asset('images/items/fallback.svg') }}"
                            alt="{{ $item->product_name }}"
                            width="96"
                            height="96"
                            style="display: block; width: 96px; height: 96px; border-radius: 14px; object-fit: cover; background: #081022;"
                        >
                        <div style="flex: 1;">
                            <p style="margin: 0 0 4px; font-size: 16px; font-weight: 700;">{{ $item->product_name }}</p>
                            <p style="margin: 0 0 4px; color: #8e99b8;">Size {{ $item->product_size }} · Quantity {{ $item->quantity }}</p>
                            <p style="margin: 0; color: #edf3ff;">{{ number_format($item->line_total_cents / 100, 2) }} EUR</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="padding: 18px; background: #121b34; border-radius: 16px;">
                <h2 style="margin: 0 0 12px; font-size: 18px;">Order totals</h2>
                <p style="margin: 0 0 6px;"><strong>Subtotal:</strong> {{ number_format($order->subtotal_cents / 100, 2) }} EUR</p>
                <p style="margin: 0 0 6px;"><strong>Shipping:</strong> {{ number_format($order->shipping_cents / 100, 2) }} EUR</p>
                <p style="margin: 0;"><strong>Total:</strong> {{ number_format($order->total_cents / 100, 2) }} EUR</p>
            </div>
        </div>
    </body>
</html>
