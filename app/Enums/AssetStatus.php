<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Borrowed = 'borrowed';
    case Maintenance = 'maintenance';
    case Retired = 'retired';
}
