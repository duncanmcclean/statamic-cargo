{{ $taxIncludedInPrices = config('statamic.cargo.tax_engine_config.included_in_prices') }}

@component('mail::message')
    # {{ __('Order Shipped') }}

    {{ __('This email is to confirm that your order (**#:orderNumber**) has been marked as shipped. An overview of your order is shown below:', [
        'orderNumber' => $order->orderNumber(),
    ]) }}

    @component('mail::table')
        | {{ __('Items') }}       | {{ __('Quantity') }}         | {{ __('Total') }} |
        | :--------- | :------------- | :----- |
        @foreach ($order->lineItems() as $lineItem)
            | [{{ $lineItem->product()->get('title') }}]({{ optional($lineItem->product()->resource())->absoluteUrl() }}) | {{ $lineItem->quantity() }} | {{ \DuncanMcClean\Cargo\Support\Money::format($taxIncludedInPrices ? $lineItem->totalIncludingTax() : $lineItem->total(), $site) }} |
        @endforeach
        | | {{ __('Subtotal') }}: | {{ \DuncanMcClean\Cargo\Support\Money::format($taxIncludedInPrices ? $order->itemsTotalWithTax() : $order->itemsTotal(), $site) }}
        @if($order->coupon())
            | | {{ __('Coupon') }}: | -{{ \DuncanMcClean\Cargo\Support\Money::format($order->discountTotal(), $site) }}
        @endif
        | | {{ __('ShippingMethod') }}: | {{ \DuncanMcClean\Cargo\Support\Money::format($taxIncludedInPrices ? $order->shippingTotalWithTax() : $order->shippingTotal(), $site) }}
        @if(!$taxIncludedInPrices)
            | | {{ __('Tax') }}: | {{ \DuncanMcClean\Cargo\Support\Money::format($order->taxTotal(), $site) }}
        @endif
        | | **{{ __('Total') }}:** | {{ \DuncanMcClean\Cargo\Support\Money::format($order->grandTotal(), $site) }}
        | | |
    @endcomponent

    ## {{ __('Customer Details') }}

    @if($order->customer())
        * **{{ __('Name') }}:** {{ $order->customer()->name() }}
        * **{{ __('Email') }}:** {{ $order->customer()->email() }}
    @endif

    @if($order->billingAddress())
        * **{{ __('Billing Address') }}:** {{ $order->billingAddress()->__toString() }}
    @endif

    @if($order->shippingAddress())
        * **{{ __('ShippingMethod Address') }}:** {{ $order->shippingAddress()->__toString() }}
    @endif

    <br>

    {{ __('If you have any questions about your order, please get in touch.') }}

    {{ __('Thanks') }},<br>
    {{ config('app.name') }}
@endcomponent
