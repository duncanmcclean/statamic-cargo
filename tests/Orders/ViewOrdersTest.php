<?php

namespace Tests\Orders;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\TestCase;

class ViewOrdersTest extends TestCase
{
    #[Test]
    public function can_view_order()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.orders.index'))
            ->assertOk();
    }

    #[Test]
    public function cant_view_order_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('cargo.orders.index'))
            ->assertRedirect('/cp');
    }
}
