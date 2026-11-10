<?php

namespace App\Enums;

enum AnnouncementCategory: string
{
    case Operational = 'operational';
    case Maintenance = 'maintenance';
    case Policy = 'policy';
    case Procurement = 'procurement';
}
