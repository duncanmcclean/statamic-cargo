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
        $discount = Discount::make()->name('Bazqux 25%')->type(DiscountType::Percentage)->amount(25);
        $discount->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'name' => 'Bazqux 50%',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertOk()
            ->assertSee('Bazqux 50%');

        $discount = $discount->fresh();

        $this->assertEquals($discount->name(), 'Bazqux 50%');
        $this->assertEquals($discount->type(), DiscountType::Percentage);
        $this->assertEquals($discount->amount(), 50);
        $this->assertEquals($discount->get('customer_eligibility'), 'all');
    }

    #[Test]
    public function cant_update_discount_without_permissions()
    {
        $discount = Discount::make()->name('Bazqux 25%')->type(DiscountType::Percentage)->amount(25);
        $discount->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'name' => 'Bazqux 50%',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertRedirect('/cp');

        $this->assertNotEquals($discount->fresh()->name(), 'Bazqux 50%');
    }

    #[Test]
    public function cant_update_discount_with_invalid_characters_in_code()
    {
        $discount = Discount::make()->name('Foobar')->code('FOOBAR25')->type(DiscountType::Percentage)->amount(25);
        $discount->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'name' => 'Foobar',
                'code' => 'FOOB;//-\(R',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNotEquals($discount->fresh()->code(), 'FOOB;//-\(R');
    }

    #[Test]
    public function cant_update_discount_with_lowercase_code()
    {
        $discount = Discount::make()->name('Foobar')->code('FOOBAR25')->type(DiscountType::Percentage)->amount(25);
        $discount->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'name' => 'Foobar',
                'code' => 'foobar',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNotEquals($discount->fresh()->code(), 'foobar');
    }

    #[Test]
    public function cant_update_discount_with_duplicate_code()
    {
        Discount::make()->code('FOOBAR')->type(DiscountType::Percentage)->amount(50)->save();
        $discount = tap(Discount::make()->code('FOOBAR25')->type(DiscountType::Percentage)->amount(25))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->id()), [
                'name' => 'Foobar',
                'code' => 'FOOBAR',
                'type' => 'percentage',
                'amount' => ['mode' => 'percentage', 'value' => 50],
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNotEquals($discount->fresh()->code(), 'FOOBAR');
    }
}
