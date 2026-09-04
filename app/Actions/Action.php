<?php

namespace App\Actions;

abstract class Action
{
    abstract public function handle(mixed ...$args): mixed;
}
