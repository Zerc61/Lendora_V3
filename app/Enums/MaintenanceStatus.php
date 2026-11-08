<?php

namespace App\Enums;

enum MaintenanceStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Cancelled = 'cancelled';
}
