<?php

namespace Tests\Unit\Services;

use App\Services\MoneyConversionService;
use PHPUnit\Framework\TestCase;

class MoneyConversionServiceTest extends TestCase
{
    public function test_it_keeps_amount_when_currency_matches_base_currency(): void
    {
        $service = new MoneyConversionService;

        $this->assertSame(12345, $service->toBaseMinor(12345, 'RUB', 'RUB', 1.2));
    }

    public function test_it_converts_amount_with_rounding(): void
    {
        $service = new MoneyConversionService;

        $this->assertSame(101, $service->toBaseMinor(100, 'USD', 'RUB', 1.005));
    }
}
