<?php

namespace Tests\Stache\Stores;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Discounts\DiscountType;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;
use DuncanMcClean\Cargo\Facades;

class DiscountsStoreTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Stache::store('discounts');
        $this->store->directory($this->directory = Path::tidy(__DIR__.'/../__fixtures__/content/cargo/discounts'));
    }

    #[Test]
    public function it_makes_discount_instances_from_files()
    {
        $item = $this->store->makeItemFromFile(
            $this->directory.'/BLACKFRIDAY.yaml',
            "id: foo\namount: 25\ntype: percentage\nbar: baz",
        );

        $this->assertInstanceOf(Discount::class, $item);
        $this->assertEquals('foo', $item->id());
        $this->assertEquals('BLACKFRIDAY', $item->code());
        $this->assertEquals(25, $item->amount());
        $this->assertEquals(DiscountType::Percentage, $item->type());
        $this->assertEquals('baz', $item->get('bar'));
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $discount = Facades\Discount::make()
            ->id('123')
            ->code('BLACKFRIDAY')
            ->type(DiscountType::Percentage);

        $this->store->save($discount);

        $this->assertStringEqualsFile($path = $this->directory.'/BLACKFRIDAY.yaml', $discount->fileContents());
        @unlink($path);
        $this->assertFileDoesNotExist($path);

        $this->assertEquals($path, $this->store->paths()->get('123'));
    }

    #[Test]
    public function it_saves_to_disk_with_modified_path()
    {
        $discount = Facades\Discount::make()
            ->id('123')
            ->code('BLACKFRIDAY')
            ->type(DiscountType::Percentage);

        $this->store->save($discount);

        $this->assertStringEqualsFile($initialPath = $this->directory.'/BLACKFRIDAY.yaml', $discount->fileContents());
        $this->assertEquals($initialPath, $this->store->paths()->get('123'));

        $discount->code('CYBERWEEKEND');
        $discount->save();

        $this->assertStringEqualsFile($path = $this->directory.'/CYBERWEEKEND.yaml', $discount->fileContents());
        $this->assertEquals($path, $this->store->paths()->get('123'));

        @unlink($initialPath);
        @unlink($path);
    }
}