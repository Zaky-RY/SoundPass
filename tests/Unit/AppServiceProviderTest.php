<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_rupiah_blade_directive_formats_amount_correctly(): void
    {
        $rendered = Blade::render('@rupiah(150000)');
        $this->assertSame('Rp 150.000', trim($rendered));

        $renderedVariable = Blade::render('@rupiah($amount)', ['amount' => 2500000]);
        $this->assertSame('Rp 2.500.000', trim($renderedVariable));

        $renderedZero = Blade::render('@rupiah(0)');
        $this->assertSame('Rp 0', trim($renderedZero));
    }
}
