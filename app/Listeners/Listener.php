<?php

namespace App\Listeners;

abstract class Listener
{
    abstract public function handle(object $event): void;
}
