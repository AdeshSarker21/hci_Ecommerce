<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

abstract class Service
{
    protected function logError(string $action, \Throwable $e): void
    {
        Log::error("Service error in {$action}: {$e->getMessage()}", [
            'exception' => $e,
            'service' => static::class,
        ]);
    }
}
