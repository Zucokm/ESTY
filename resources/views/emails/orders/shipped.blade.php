<x-mail::message>
# Great News! Your Order has Shipped

Your order **#VR-{{ $order->id }}** from ESTY has been handed over to our delivery partners and is on its way to your address:

> {{ $order->shipping_address }}

<x-mail::button :url="url('/orders')">
Track Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
