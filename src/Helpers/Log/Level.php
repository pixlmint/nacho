<?php

namespace Nacho\Helpers\Log;

enum Level
{
    case DEBUG;
    case INFO;
    case NOTICE;
    case DEPRECATION;
    case WARNING;
    case ERROR;
}
