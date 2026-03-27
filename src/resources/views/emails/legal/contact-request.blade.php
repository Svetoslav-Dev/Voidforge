<x-mail::message>
# New VoidForgeStore request

**Topic:** {{ match ($payload['topic']) {
    'privacy' => 'Privacy',
    'returns' => 'Returns',
    'order_support' => 'Order support',
    default => 'General',
} }}

**Name:** {{ $payload['name'] }}

**Email:** {{ $payload['email'] }}

@if (filled($payload['order_reference'] ?? null))
**Order reference:** {{ $payload['order_reference'] }}

@endif
**Message:**

{{ $payload['message'] }}
</x-mail::message>
