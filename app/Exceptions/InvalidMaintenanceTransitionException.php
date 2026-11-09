<?php

namespace App\Exceptions;

use App\Enums\MaintenanceStatus;
use App\Models\Maintenance;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidMaintenanceTransitionException extends HttpException
{
    public static function for(Maintenance $maintenance, MaintenanceStatus $to): self
    {
        return new self(
            422,
            __('messages.maintenance.invalid_transition', [
                'from' => $maintenance->status->value,
                'to' => $to->value,
            ]),
        );
    }
}
