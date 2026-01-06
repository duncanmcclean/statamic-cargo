<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FormatMoneyTest extends TestCase
{
    public static function amountProvider(): array
    {
        return [
            [null, null],
            ['1599', '£15.99'],
            ['15.99', '£15.99'],
            [1599, '£15.99'],
            [15.99, '£15.99'],
        ];
    }

    #[Test]
    #[DataProvider('amountProvider')]
    public function it_formats_money($input, $expected)
    {
        $this->assertEquals($expected, $this->modify($input));
    }

    #[Test]
    public function exception_is_thrown_when_non_numeric_value_is_provided()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The format_money modifier requires a numeric value.');

        $this->modify('Hello World!');
    }

    private function modify($value)
    {
        return Modify::value($value)->formatMoney()->fetch();
    }
}
