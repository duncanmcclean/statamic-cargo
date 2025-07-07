<?php

namespace Tests\Discounts;

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
        $discount = Discount::make()->title('Bazqux 25%')->type('percentage_off')->set('percentage_off', 25);
        $discount->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->handle()), [
                'title' => 'Bazqux 50%',
                'type' => 'percentage_off',
                'percentage_off' => 50,
            ])
            ->assertOk();

        $discount = $discount->fresh();

        $this->assertEquals($discount->title(), 'Bazqux 50%');
        $this->assertEquals($discount->type(), 'percentage_off');
        $this->assertEquals($discount->get('percentage_off'), 50);
    }

    #[Test]
    public function cant_update_discount_without_permissions()
    {
        $discount = Discount::make()->title('Bazqux 25%')->type('percentage_off')->set('percentage_off', 25);
        $discount->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('cargo.discounts.update', $discount->handle()), [
                'title' => 'Bazqux 50%',
                'type' => 'percentage_off',
                'percentage_off' => 50,
                'customer_eligibility' => 'all',
            ])
            ->assertRedirect('/cp');

        $this->assertNotEquals($discount->fresh()->title(), 'Bazqux 50%');
    }

    #[Test]
    public function cant_update_discount_with_invalid_characters_in_code()
    {
        $discount = Discount::make()->title('Foobar')->set('discount_code', 'FOOBAR25')->type('percentage_off')->set('percentage_off', 25);
        $discount->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->handle()), [
                'title' => 'Foobar',
                'discount_code' => 'FOOB;//-\(R',
                'type' => 'percentage_off',
                'percentage_off' => 50,
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('discount_code');

        $this->assertNotEquals($discount->fresh()->get('discount_code'), 'FOOB;//-\(R');
    }

    #[Test]
    public function cant_update_discount_with_lowercase_code()
    {
        $discount = Discount::make()->title('Foobar')->set('discount_code', 'FOOBAR25')->type('percentage_off')->set('percentage_off', 25);
        $discount->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->handle()), [
                'title' => 'Foobar',
                'discount_code' => 'foobar',
                'type' => 'percentage_off',
                'percentage_off' => 50,
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('discount_code');

        $this->assertNotEquals($discount->fresh()->get('discount_code'), 'foobar');
    }

    #[Test]
    public function cant_update_discount_with_duplicate_code()
    {
        Discount::make()->title('Foobar')->set('discount_code', 'FOOBAR')->type('percentage_off')->set('percentage_off', 50)->save();
        $discount = tap(Discount::make()->title('Foobar 25')->set('discount_code', 'FOOBAR25')->type('percentage_off')->set('percentage_off', 25))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.discounts.update', $discount->handle()), [
                'title' => 'Foobar',
                'discount_code' => 'FOOBAR',
                'type' => 'percentage_off',
                'percentage_off' => 50,
                'customer_eligibility' => 'all',
            ])
            ->assertSessionHasErrors('discount_code');

        $this->assertNotEquals($discount->fresh()->get('discount_code'), 'FOOBAR');
    }
}
