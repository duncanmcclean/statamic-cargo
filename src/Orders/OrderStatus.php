<?php

namespace DuncanMcClean\Cargo\Orders;

use DuncanMcClean\Cargo\Events\OrderCancelled;
use DuncanMcClean\Cargo\Events\OrderPaymentPending;
use DuncanMcClean\Cargo\Events\OrderPaymentReceived;
use DuncanMcClean\Cargo\Events\OrderReturned;
use DuncanMcClean\Cargo\Events\OrderShipped;

enum OrderStatus: string
{
    case PaymentPending = 'payment_pending';
    case PaymentReceived = 'payment_received';
    case Shipped = 'shipped';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    public static function label($status): string
    {
        return match ($status) {
            self::PaymentPending => __('Payment Pending'),
            self::PaymentReceived => __('Payment Received'),
            self::Shipped => __('Shipped'),
            self::Returned => __('Returned'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public static function event($status): string
    {
        return match ($status) {
            self::PaymentPending => OrderPaymentPending::class,
            self::PaymentReceived => OrderPaymentReceived::class,
            self::Shipped => OrderShipped::class,
            self::Returned => OrderReturned::class,
            self::Cancelled => OrderCancelled::class,
        };
    }
}
