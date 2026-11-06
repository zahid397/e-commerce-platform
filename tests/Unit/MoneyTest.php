<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_prices_and_line_totals_use_integer_minor_units(): void
    {
        $this->assertSame(1234, Money::minor('12.34'));
        $this->assertSame(1050, Money::minor('10.5'));
        $this->assertSame('30.30', Money::decimal(Money::minor('10.10') * 3));
        $this->assertSame('247499999975.25', Money::decimal(Money::minor('99999999.99') * 25 * 99));
    }
}
