<?php

namespace Tests\Discounts;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateDiscountsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_create_discount()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.discounts.create'))
            ->assertOk()
            ->assertSee('Create Discount');
    }

    #[Test]
    public function cant_create_discount_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('cargo.discounts.create'))
            ->assertRedirect('/cp');
    }
}
