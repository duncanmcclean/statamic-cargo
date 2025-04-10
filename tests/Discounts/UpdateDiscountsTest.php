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

class UpdateDiscountsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_update_discounts()
    {
        $discount = tap(Discount::make()->code('FOOBAR25'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'name' => 'Bazqux',
                'code' => 'BAZQUX50',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertOk()
            ->assertSee('BAZQUX50');

        $discount = $discount->fresh();

        $this->assertEquals($discount->code(), 'BAZQUX50');
        $this->assertEquals($discount->type(), DiscountType::Percentage);
        $this->assertEquals($discount->amount(), 50);
        $this->assertEquals($discount->get('customer_eligibility'), 'all');
    }

    #[Test]
    public function cant_update_discount_without_permissions()
    {
        $discount = tap(Discount::make()->code('FOOBAR25'))->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'code' => 'BAZQUX50',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertRedirect('/cp');
    }

    #[Test]
    public function cant_update_discount_with_invalid_characters_in_code()
    {
        $discount = tap(Discount::make()->code('FOOBAR25'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'code' => 'FOOB;//-\(R',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $discount = $discount->fresh();

        $this->assertNotEquals($discount->code(), 'FOOB;//-\(R');
    }

    #[Test]
    public function cant_update_discount_with_lowercase_code()
    {
        $discount = tap(Discount::make()->code('FOOBAR25'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'code' => 'foobar',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $discount = $discount->fresh();

        $this->assertNotEquals($discount->code(), 'foobar');
    }

    #[Test]
    public function cant_update_discount_with_duplicate_code()
    {
        Discount::make()->code('FOOBAR')->save();
        $discount = tap(Discount::make()->code('FOOBAR25'))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'code' => 'FOOBAR',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');
    }
}
