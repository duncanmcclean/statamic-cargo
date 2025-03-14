{{ $taxIncludedInPrices = config('statamic.cargo.tax_engine_config.included_in_prices') }}

@component('mail::message')
    # {{ __('New Order') }}

    {{ __('This email is to confirm that a new order has been placed. An overview of the order is shown below:') }}

    **{{ __('Order Number') }}:** #{{ $order->orderNumber() }}

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

    {{ __('Thanks') }},<br>
    {{ config('app.name') }}
@endcomponent
