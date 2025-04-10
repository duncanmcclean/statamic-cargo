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

class StoreDiscountsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();

        // Again, don't know why, but this fixes things.
        Discount::all()->each->delete();
    }

    #[Test]
    public function can_store_discount()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('cargo.discounts.store'), [
                'name' => 'Bazqux',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertOk()
            ->assertSee('Bazqux');

        $discount = Discount::query()->where('name', 'Bazqux')->first();

        $this->assertEquals($discount->name(), 'Bazqux');
        $this->assertEquals($discount->type(), DiscountType::Percentage);
        $this->assertEquals($discount->amount(), 50);
        $this->assertEquals($discount->get('customer_eligibility'), 'all');
    }

    #[Test]
    public function cant_store_discount_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->post(cp_route('cargo.discounts.store'), [
                'name' => 'Bazqux',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
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
                'code' => 'FOOB;//-\(R',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNull(Discount::findByCode('FOOB;//-\(R'));
    }

    #[Test]
    public function cant_store_discount_with_lowercase_code()
    {
        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('cargo.discounts.store'), [
                'name' => 'Foobar',
                'code' => 'foobar',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNull(Discount::findByCode('foobar'));
    }

    #[Test]
    public function cant_store_discount_with_duplicate_code()
    {
        Discount::make()->code('FOOBAR')->type(DiscountType::Percentage)->amount(50)->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('cargo.discounts.store'), [
                'name' => 'Foobar',
                'code' => 'FOOBAR',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');
    }
}
