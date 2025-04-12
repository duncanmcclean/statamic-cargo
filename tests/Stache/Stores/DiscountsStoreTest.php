<?php

namespace Tests\Stache\Stores;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use DuncanMcClean\Cargo\Facades;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

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
            $this->directory.'/black-friday.yaml',
            "id: foo\nname: Black Friday\ntype: percentage_off\npercentage_off: 25\nbar: baz",
        );

        $this->assertInstanceOf(Discount::class, $item);
        $this->assertEquals('foo', $item->id());
        $this->assertEquals('Black Friday', $item->name());
        $this->assertEquals('percentage_off', $item->type());
        $this->assertEquals(25, $item->get('percentage_off'));
        $this->assertEquals('baz', $item->get('bar'));
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $discount = Facades\Discount::make()
            ->id('123')
            ->name('Black Friday')
            ->type('percentage_off');

        $this->store->save($discount);

        $this->assertStringEqualsFile($path = $this->directory.'/black-friday.yaml', $discount->fileContents());
        @unlink($path);
        $this->assertFileDoesNotExist($path);

        $this->assertEquals($path, $this->store->paths()->get('123'));
    }

    #[Test]
    public function it_saves_to_disk_with_modified_path()
    {
        $discount = Facades\Discount::make()
            ->id('123')
            ->name('Black Friday')
            ->type('percentage_off');

        $this->store->save($discount);

        $this->assertStringEqualsFile($initialPath = $this->directory.'/black-friday.yaml', $discount->fileContents());
        $this->assertEquals($initialPath, $this->store->paths()->get('123'));

        $discount->name('Cyber Weekend');
        $discount->save();

        $this->assertStringEqualsFile($path = $this->directory.'/cyber-weekend.yaml', $discount->fileContents());
        $this->assertEquals($path, $this->store->paths()->get('123'));

        @unlink($initialPath);
        @unlink($path);
    }
}
