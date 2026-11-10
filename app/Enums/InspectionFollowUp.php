<?php

namespace App\Enums;

enum InspectionFollowUp: string
{
    case None = 'none';
    case Maintenance = 'maintenance';
    case Bap = 'bap';
}
