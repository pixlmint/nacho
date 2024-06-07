<?php

namespace Nacho\Contracts\Hooks;

use Nacho\Models\PicoPage;

interface PostHandleUpdate
{
    public function call(PicoPage $updatedEntry): void;
}
