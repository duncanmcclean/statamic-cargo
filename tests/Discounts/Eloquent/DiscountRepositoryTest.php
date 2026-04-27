<?php

namespace Tests\Discounts\Eloquent;

use DuncanMcClean\Cargo\Discounts\Eloquent\DiscountModel;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Statamic;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class DiscountRepositoryTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk, RefreshDatabase;

    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.cargo.discounts', [
            'repository' => 'eloquent',
            'model' => \DuncanMcClean\Cargo\Discounts\Eloquent\DiscountModel::class,
            'table' => 'cargo_discounts',
        ]);

        $this->app->bind('cargo.discounts.eloquent.model', function () {
            return config('statamic.cargo.discounts.model', \DuncanMcClean\Cargo\Discounts\Eloquent\DiscountModel::class);
        });

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Discounts\DiscountRepository::class,
            \DuncanMcClean\Cargo\Discounts\Eloquent\DiscountRepository::class
        );

        $this->repo = $this->app->make(\DuncanMcClean\Cargo\Contracts\Discounts\DiscountRepository::class);
    }

    #[Test]
    public function can_find_discounts()
    {
        $model = DiscountModel::create([
            'handle' => 'summer-sale',
            'title' => 'Summer Sale',
            'type' => 'percentage_off',
            'data' => ['percentage_off' => 10, 'discount_code' => 'SUMMER'],
        ]);

        $discount = $this->repo->find('summer-sale');

        $this->assertEquals('summer-sale', $discount->handle());
        $this->assertEquals('Summer Sale', $discount->title());
        $this->assertEquals('percentage_off', $discount->type());
        $this->assertEquals(10, $discount->get('percentage_off'));
        $this->assertEquals('SUMMER', $discount->get('discount_code'));
    }

    #[Test]
    public function can_save_a_discount()
    {
        $discount = Discount::make()
            ->handle('summer-sale')
            ->title('Summer Sale')
            ->type('percentage_off')
            ->data(['percentage_off' => 10, 'discount_code' => 'SUMMER']);

        $this->repo->save($discount);

        $this->assertDatabaseHas('cargo_discounts', [
            'handle' => 'summer-sale',
            'title' => 'Summer Sale',
            'type' => 'percentage_off',
            'data->percentage_off' => 10,
            'data->discount_code' => 'SUMMER',
        ]);
    }

    #[Test]
    public function can_delete_a_discount()
    {
        $discount = Discount::make()
            ->handle('summer-sale')
            ->title('Summer Sale')
            ->type('percentage_off');

        $discount->save();

        $this->repo->delete($discount);

        $this->assertDatabaseMissing('cargo_discounts', [
            'handle' => 'summer-sale',
        ]);
    }
}
