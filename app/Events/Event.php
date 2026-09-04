<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class Event
{
    use Dispatchable, SerializesModels;

    public function __construct()
    {
        //
    }
}
