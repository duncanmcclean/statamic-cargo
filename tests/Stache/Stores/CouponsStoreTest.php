<?php

namespace Tests\Stache\Stores;

use DuncanMcClean\Cargo\Contracts\Coupons\Coupon;
use DuncanMcClean\Cargo\Coupons\CouponType;
use DuncanMcClean\Cargo\Facades;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CouponsStoreTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Stache::store('coupons');
        $this->store->directory($this->directory = Path::tidy(__DIR__.'/../__fixtures__/content/cargo/coupons'));
    }

    #[Test]
    public function it_makes_coupon_instances_from_files()
    {
        $item = $this->store->makeItemFromFile(
            $this->directory.'/BLACKFRIDAY.yaml',
            "id: foo\namount: 25\ntype: percentage\nbar: baz",
        );

        $this->assertInstanceOf(Coupon::class, $item);
        $this->assertEquals('foo', $item->id());
        $this->assertEquals('BLACKFRIDAY', $item->code());
        $this->assertEquals(25, $item->amount());
        $this->assertEquals(CouponType::Percentage, $item->type());
        $this->assertEquals('baz', $item->get('bar'));
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $coupon = Facades\Coupon::make()
            ->id('123')
            ->code('BLACKFRIDAY')
            ->type(CouponType::Percentage);

        $this->store->save($coupon);

        $this->assertStringEqualsFile($path = $this->directory.'/BLACKFRIDAY.yaml', $coupon->fileContents());
        @unlink($path);
        $this->assertFileDoesNotExist($path);

        $this->assertEquals($path, $this->store->paths()->get('123'));
    }

    #[Test]
    public function it_saves_to_disk_with_modified_path()
    {
        $coupon = Facades\Coupon::make()
            ->id('123')
            ->code('BLACKFRIDAY')
            ->type(CouponType::Percentage);

        $this->store->save($coupon);

        $this->assertStringEqualsFile($initialPath = $this->directory.'/BLACKFRIDAY.yaml', $coupon->fileContents());
        $this->assertEquals($initialPath, $this->store->paths()->get('123'));

        $coupon->code('CYBERWEEKEND');
        $coupon->save();

        $this->assertStringEqualsFile($path = $this->directory.'/CYBERWEEKEND.yaml', $coupon->fileContents());
        $this->assertEquals($path, $this->store->paths()->get('123'));

        @unlink($initialPath);
        @unlink($path);
    }
}
