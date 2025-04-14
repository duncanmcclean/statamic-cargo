<?php

namespace Tests\Discounts;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\TestCase;

class ViewDiscountsTest extends TestCase
{
    #[Test]
    public function can_view_discounts()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.discounts.index'))
            ->assertOk();
    }

    #[Test]
    public function cant_view_discounts_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('cargo.discounts.index'))
            ->assertRedirect('/cp');
    }
}
