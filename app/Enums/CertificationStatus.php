<?php

namespace App\Enums;

enum CertificationStatus: string
{
    case Valid = 'valid';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
