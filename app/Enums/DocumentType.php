<?php

namespace App\Enums;

enum DocumentType: string
{
    case Sop = 'sop';
    case Form = 'form';
    case Manual = 'manual';
    case Certificate = 'certificate';
    case Other = 'other';
}
