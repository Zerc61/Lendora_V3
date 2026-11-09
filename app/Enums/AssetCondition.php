<?php

namespace App\Enums;

enum AssetCondition: string
{
    case Good = 'good';
    case MinorDamage = 'minor_damage';
    case Damaged = 'damaged';
    case Lost = 'lost';
}
