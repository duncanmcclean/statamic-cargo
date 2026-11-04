<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class PackingSlipTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_view_packing_slip()
    {
        $order = tap(Order::make()->orderNumber(1002))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.orders.packing-slip', $order->id()))
            ->assertOk()
            ->assertSee('Packing Slip for Order #1002');
    }

    #[Test]
    public function only_passes_safe_config_to_the_view()
    {
        $order = tap(Order::make())->save();

        $response = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.orders.packing-slip', $order->id()))
            ->assertOk();

        $config = $response->viewData('config');

        $this->assertEquals(config('app.name'), $config['app']['name']);
        $this->assertArrayNotHasKey('key', $config['app']);
        $this->assertArrayNotHasKey('database', $config);
    }

    #[Test]
    public function cant_view_packing_slip_without_permissions()
    {
        $order = tap(Order::make())->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('cargo.orders.packing-slip', $order->id()))
            ->assertRedirect('/cp');
    }
}
