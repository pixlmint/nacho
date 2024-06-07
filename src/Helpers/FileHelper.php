<?php

namespace Nacho\Helpers;

use Nacho\Models\PicoPage;

class FileHelper
{
    public function getFiles($directory): array
    {
        $directory = rtrim($directory, '/');
        $fileExtensionLength = strlen('.md');
        $result = [];

        $files = $this->indexFirst(scandir($directory));
        if ($files !== false) {
            foreach ($files as $file) {
                // exclude special dirs . and ..
                // exclude files ending with a ~ (vim/nano backup) or # (emacs backup)
                if (($file === '.' || $file === '..') || in_array(substr($file, -1), array('~', '#'), true)) {
                    continue;
                }

                if (is_dir($directory . '/' . $file)) {
                    // get files recursively
                    $result = array_merge($result, $this->getFiles($directory . '/' . $file));
                } elseif (substr($file, -$fileExtensionLength) === '.md') {
                    $result[] = $directory . '/' . $file;
                }
            }
        }

        return $result;
    }

    /**
     * @param PicoPage $page
     * @return bool success
     */
    public function storePage(PicoPage $page): bool
    {
        $pageContent = MetaHelper::createMetaString($page->meta->toArray()) . $page->raw_content;
        if (!$pageContent || !$page->id || !$page->file) {
            return false;
        }
        $filePath = $page->file;
        return $this->storeFileContents($filePath, $pageContent);
    }

    public function storeFileContents(string $filePath, string $fileContent): false|int
    {
        return file_put_contents($filePath, $fileContent);
    }

    public function move(string $source, string $target): bool
    {
        return rename($source, $target);
    }

    public static function loadFileContent($file): string
    {
        return file_get_contents($file);
    }

    private function indexFirst(array $files): array
    {
        usort($files, function ($a, $b) {
            if ($a === 'index.md') {
                return -1;
            }

            if ($b === 'index.md') {
                return 1;
            }

            return strcmp($a, $b);
        });

        return $files;
    }
}