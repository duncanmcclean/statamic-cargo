<?php

namespace Tests\Discounts;

use DuncanMcClean\Cargo\Facades\Discount;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreDiscountsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_store_discount()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('cargo.discounts.store'), [
                'name' => 'Bazqux',
                'type' => 'percentage_off',
                'percentage_off' => 50,
            ])
            ->assertOk()
            ->assertSee('Bazqux');

        $discount = Discount::query()->where('name', 'Bazqux')->first();

        $this->assertEquals($discount->name(), 'Bazqux');
        $this->assertEquals($discount->type(), 'percentage_off');
        $this->assertEquals($discount->get('percentage_off'), 50);
    }

    #[Test]
    public function cant_store_discount_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->post(cp_route('cargo.discounts.store'), [
                'name' => 'Bazqux',
                'type' => 'percentage_off',
                'percentage_off' => 50,
                'customer_eligibility' => 'all',
            ])
            ->assertRedirect('/cp');

        $this->assertNull(Discount::query()->where('name', 'Bazqux')->first());
    }

    #[Test]
    public function cant_store_discount_with_invalid_characters_in_code()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('cargo.discounts.store'), [
                'name' => 'Foobar',
                'discount_code' => 'FOOB;//-\(R',
                'type' => 'percentage_off',
                'percentage_off' => 50,
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('discount_code');

        $this->assertNull(Discount::findByDiscountCode('FOOB;//-\(R'));
    }

    #[Test]
    public function cant_store_discount_with_lowercase_code()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('cargo.discounts.store'), [
                'name' => 'Foobar',
                'discount_code' => 'foobar',
                'type' => 'percentage_off',
                'percentage_off' => 50,
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('discount_code');

        $this->assertNull(Discount::findByDiscountCode('foobar'));
    }

    #[Test]
    public function cant_store_discount_with_duplicate_code()
    {
        Discount::make()->set('discount_code', 'FOOBAR')->type('percentage_off')->set('percentage_off', 50)->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('cargo.discounts.store'), [
                'name' => 'Foobar',
                'discount_code' => 'FOOBAR',
                'type' => 'percentage_off',
                'percentage_off' => 50,
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('discount_code');
    }
}
