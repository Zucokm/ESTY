<x-mail::message>
# Order Confirmation

Thank you for shopping at ESTY. Your order **#VR-{{ $order->id }}** has been placed successfully and is currently pending processing.

**Total Amount:** ${{ number_format($order->total_amount, 2) }}
@if($order->discount_amount > 0)
**Discount Applied:** -${{ number_format($order->discount_amount, 2) }}
@endif

We will notify you once your order is shipped.

<x-mail::button :url="url('/orders')">
View Your Orders
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
