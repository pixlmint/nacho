<?php

namespace Nacho\Contracts\Hooks;

interface PostCallFunction
{
    public function call($returnedResponse);
}
