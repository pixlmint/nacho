<?php

namespace Nacho\Helpers;

use DateTime;
use Exception;
use Nacho\Contracts\RequestInterface;
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

        $strUpdateTime = self::convertDateValue($oldPage->meta->dateUpdated);

        $oldDateUpdate = new DateTime($strUpdateTime);
        $newDateUpdate = new DateTime($tryUpdate);

        return $newDateUpdate >= $oldDateUpdate;
    }

    public function hasValidUpdateTime(RequestInterface $request): bool
    {
        $lastUpdateValue = self::convertDateValue($request->getBody()['lastUpdate']);

        try {
            new DateTime($lastUpdateValue);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    private static function convertDateValue(mixed $dtValue): string
    {
        if (is_numeric($dtValue)) {
            $dtValue = date('Y-m-d H:i:s', $dtValue);
        }
        return $dtValue;
    }
}