<?php

namespace Nacho\Security;

class PageSecurityStatus
{
    /**
     * Anyone (above Guest) can view and edit
     */
    const PRIVATE = 'private';

    /**
     * Anyone can View
     */
    const PUBLIC = 'public';

    /**
     * TODO: implement
     * Anyone can view, Only owner can edit
     */
    // const SECURE = 'secure';
}