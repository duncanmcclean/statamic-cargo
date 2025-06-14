<x-mail::message>
# Order #{{ $order->orderNumber }} has been confirmed

Your order has been confirmed and is now being processed. We will notify you when it has been shipped.

<x-mail::table>
| Item Description   |               |
| :----------------- | ------------: |
@foreach($order->lineItems() as $lineItem)
@php
$hasDownloads = $lineItem->hasDownloads();
$downloadUrl = URL::signedRoute('statamic.cargo.download', [
    'orderId' => $order->id(),
    'lineItem' => $lineItem->id(),
]);
@endphp
| {{ $lineItem->quantity }}x {{ $lineItem->product()->title }} @if($hasDownloads) <br><br> [Download]({{ $downloadUrl }}) @endif | {{ $lineItem->sub_total }} |
@endforeach
</x-mail::table>

<x-mail::table>
|                    |               |
| -----------------: | ------------: |
| **Subtotal** | {{ $order->sub_total }} |
@if($order->discounts)
| **Discounts** | -{{ $order->discount_total }}|
@endif
@if($order->shippingOption)
| **Shipping** ({{ $order->shippingOption()->name }}) | {{ $order->shipping_total }} |
@endif
@unless(config('statamic.cargo.taxes.price_includes_tax'))
| **Taxes** | {{ $order->tax_total }} |
@endunless
| **Total** | **{{ $order->grand_total }}** |
</x-mail::table>

<x-mail::panel>
@if($order->shippingOption)
**Shipping Option:** {{ $order->shippingOption()->name() }}
@endif

@if($order->hasShippingAddress())
**Shipping Address:** {{ $order->shippingAddress() }}
@endif

**Billing Address:** {{ $order->billingAddress() }}

**Customer:** {{ $order->customer()->name }} ({{ $order->customer()->email }})
</x-mail::panel>

Thank you for your order!<br>
{{ config('app.name') }}
</x-mail::message>
