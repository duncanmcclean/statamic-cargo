<?php

namespace Tests\Discounts;

use DuncanMcClean\Cargo\Discounts\DiscountType;
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
        $discount = tap(Discount::make()->code('FOOBAR25')->type(DiscountType::Percentage)->amount(50))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.discounts.edit', $discount->id()))
            ->assertOk()
            ->assertSee('FOOBAR25');
    }

    #[Test]
    public function cant_edit_discount_without_permissions()
    {
        $discount = tap(Discount::make()->code('FOOBAR25')->type(DiscountType::Percentage)->amount(50))->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('cargo.discounts.edit', $discount->id()))
            ->assertRedirect('/cp');
    }
}
