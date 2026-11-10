<?php

namespace App\Enums;

enum AssetAccessLevel: string
{
    case Open = 'open';
    case SupervisorLetter = 'supervisor_letter';
    case ProdiApproval = 'prodi_approval';
}
