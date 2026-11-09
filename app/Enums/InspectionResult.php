<?php

namespace App\Enums;

enum InspectionResult: string
{
    case Ok = 'ok';
    case NeedsRepair = 'needs_repair';
    case Damaged = 'damaged';
}
