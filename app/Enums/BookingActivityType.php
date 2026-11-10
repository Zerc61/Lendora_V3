<?php

namespace App\Enums;

enum BookingActivityType: string
{
    case Regular = 'regular';
    case FinalProject = 'final_project';
    case StudentOrg = 'student_org';
    case Other = 'other';
}
