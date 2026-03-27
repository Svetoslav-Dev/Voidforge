<x-mail::message>
# Request received

VoidForgeStore has received your request and queued it for review.

**Topic:** {{ match ($payload['topic']) {
    'privacy' => 'Privacy request',
    'returns' => 'Returns and refunds',
    'order_support' => 'Order support',
    default => 'General',
} }}

@if (filled($payload['order_reference'] ?? null))
**Order reference:** {{ $payload['order_reference'] }}

@endif
Support email: {{ config('legal.support_email') }}

Complaints email: {{ config('legal.complaints_email') }}

Policies:
- {{ route('legal.privacy') }}
- {{ route('legal.returns') }}
- {{ route('legal.terms') }}

You do not need to resend the same request unless your details change.
</x-mail::message>
