<?php

namespace Nacho\Helpers;

use DateTime;
use Exception;
use Nacho\Models\PicoPage;

/**
 * Class PicoVersioningHelper
 * Used to check if there are any conflicts in pico page versions.
 * E.g. if a page has been updated by two users at the same time.
 */
class PicoVersioningHelper
{
    public function canUpdateToVersion(PicoPage $oldPage, string $tryUpdate): bool
    {
        if (!$oldPage->meta->dateUpdated) {
            return true;
        }

        $strUpdateTime = $oldPage->meta->dateUpdated;
        if (is_numeric($strUpdateTime)) {
            $strUpdateTime = date('Y-m-d H:i:s', $strUpdateTime);
        }
        $oldDateUpdate = new DateTime($strUpdateTime);
        $newDateUpdate = new DateTime($tryUpdate);

        return $newDateUpdate > $oldDateUpdate;
    }
}