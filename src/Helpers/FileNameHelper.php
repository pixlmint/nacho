<?php

namespace Nacho\Helpers;

use Exception;

class FileNameHelper
{
    public static function generateFileNameFromTitle(string $title): string
    {
        $title = trim($title);
        $title = substr($title, 0, 75);
        return self::slugify($title) . '.md';
    }

    public static function slugify(string $text, string $divider = '-'): string
    {
        // replace non letter or digits by divider
        $newText = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // remove unwanted characters
        $newText = preg_replace('~[^-\w]+~', '', $newText);

        // trim
        $newText = trim($newText, $divider);

        // remove duplicate divider
        $newText = preg_replace('~-+~', $divider, $newText);

        // lowercase
        $newText = strtolower($newText);

        if (empty($newText)) {
            throw new Exception('File Name cannot be empty (' . $newText . ')');
        }

        if (str_starts_with($text, '.')) {
            $newText = '.' . $newText;
        }

        return $newText;
    }
}