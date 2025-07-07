<?php

namespace Tests\Discounts;

use DuncanMcClean\Cargo\Facades\Discount;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class EditDiscountsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_edit_discount()
    {
        $discount = tap(Discount::make()->title('Foo Bar 2025')->type('percentage_off')->set('percentage_off', 50))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.discounts.edit', $discount->handle()))
            ->assertOk()
            ->assertSee('Foo Bar 2025');
    }

    #[Test]
    public function cant_edit_discount_without_permissions()
    {
        $discount = tap(Discount::make()->title('Foo Bar 2025')->type('percentage_off')->set('percentage_off', 50))->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('cargo.discounts.edit', $discount->handle()))
            ->assertRedirect('/cp');
    }
}
