<?php

namespace Tests\Unit;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use PHPUnit\Framework\TestCase;

class AssetEnumsTest extends TestCase
{
    public function test_asset_status_values_match_master_spec(): void
    {
        $this->assertSame(
            ['available', 'reserved', 'borrowed', 'maintenance', 'retired'],
            array_column(AssetStatus::cases(), 'value'),
        );
    }

    public function test_asset_condition_values_match_master_spec(): void
    {
        $this->assertSame(
            ['good', 'minor_damage', 'damaged', 'lost'],
            array_column(AssetCondition::cases(), 'value'),
        );
    }
}
