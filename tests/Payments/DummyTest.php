<?php

namespace Tests\Payments;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Payments\Gateways\Dummy;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('payments')]
class DummyTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_can_process_a_payment()
    {
        $order = $this->makeOrder();

        (new Dummy)->process($order);

        $this->assertEquals('payment_received', $order->fresh()->status()->value);
    }

    #[Test]
    public function it_can_refund_an_order()
    {
        $order = $this->makeOrder();

        (new Dummy)->refund($order, 500);

        $this->assertEquals(500, $order->fresh()->get('amount_refunded'));
    }

    private function makeOrder(): OrderContract
    {
        $order = Order::make()
            ->status(OrderStatus::PaymentPending)
            ->grandTotal(1000)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 1, 'total' => 1000],
            ]);

        $order->save();

        return $order;
    }
}
