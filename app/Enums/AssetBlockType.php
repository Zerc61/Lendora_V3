<?php

namespace App\Enums;

enum AssetBlockType: string
{
    case Maintenance = 'maintenance';
    case Calibration = 'calibration';
    case Closure = 'closure';
    case Practical = 'practical';
}
