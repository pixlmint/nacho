<?php

namespace Nacho\Helpers;

use Nacho\Models\PicoMeta;
use Nacho\Models\PicoPage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

class PageFinder
{
    private LoggerInterface $logger;
    private FileHelper $fileHelper;
    private PageSecurityHelper $pageSecurityHelper;
    private MetaHelper $metaHelper;

    public function __construct(LoggerInterface $logger, FileHelper $fileHelper, PageSecurityHelper $pageSecurityHelper, MetaHelper $metaHelper)
    {
        $this->logger = $logger;
        $this->fileHelper = $fileHelper;
        $this->pageSecurityHelper = $pageSecurityHelper;
        $this->metaHelper = $metaHelper;
    }

    public function readPages(string $contentDir): array
    {
        $this->logger->info('Reading Pages');

        $pages = array();
        $files = $this->fileHelper->getFiles($contentDir);
        foreach ($files as $i => $file) {
            $id = substr($file, strlen($contentDir), -3);

            // skip inaccessible pages (e.g. drop "sub.md" if "sub/index.md" exists) by default
            $conflictFile = $contentDir . $id . '/index.md';
            $skipFile = in_array($conflictFile, $files, true) || null;

            if ($skipFile) {
                continue;
            }

            $id = self::parseId($id);

            $url = UrlHelper::getPageUrl($id);
            $rawMarkdown = FileHelper::loadFileContent($file);
            $rawContent = PageManager::prepareFileContent($rawMarkdown);

            $headers = $this->metaHelper->getMetaHeaders();
            try {
                $meta = $this->metaHelper->parseFileMeta($rawMarkdown, $headers);
            } catch (ParseException $e) {
                $meta = $this->metaHelper->parseFileMeta('', $headers);
                $meta['YAML_ParseError'] = $e->getMessage();
            }

            // build page data
            $page = new PicoPage();
            $page->id = $id;
            $page->url = $url;
            $page->hidden = ($meta['hidden'] || preg_match('/(?:^|\/)_/', $id));
            $page->raw_content = $rawContent;
            $page->raw_markdown = $rawMarkdown;
            $picoMeta = new PicoMeta($meta);
            $page->meta = $picoMeta;
            $page->file = $file;
            $parentPath = explode('/', $id);
            array_pop($parentPath);
            $page->meta->parentPath = implode('/', $parentPath);

            unset($rawContent, $rawMarkdown, $meta);

            if ($this->pageSecurityHelper->isPageShowingForCurrentUser($page)) {
                $pages[$id] = $page;
            }
        }

        return $pages;
    }

    private static function parseId(string $originalId): string
    {
        if (str_ends_with($originalId, '/index')) {
            $id = substr($originalId, 0, -6);
            if (!$id) {
                $id = '/';
            }
        } else {
            $id = $originalId;
        }

        return $id;
    }

    /**
     * Return if the given path is a subpath of the given parent path(s)
     */
    public static function isSubPath(string $path, string $parentPath): bool
    {
        return str_starts_with($path, $parentPath) && $path !== $parentPath;
    }

    private static function isDirectChild(string $path, string $parentPath): bool
    {
        if (!self::isSubPath($path, $parentPath)) {
            return false;
        }

        if ($parentPath === '/') {
            if (count(explode('/', $path)) === 2) {
                return true;
            }
            return false;
        }

        return count(explode('/', $path)) - 1 === count(explode('/', $parentPath));
    }

    public function findChildPages(string $id, PicoPage &$parentPage, array $pages): PicoPage
    {
        foreach ($pages as $childId => $page) {
            if (isset($page->meta->min_role)) {
                if (!$this->pageSecurityHelper->isPageShowingForCurrentUser($page)) {
                    continue;
                }
            }
            if (self::isDirectChild($childId, $id)) {
                $page = $this->findChildPages($childId, $page, $pages);
                $parentPage->children[$childId] = $page;
            }
        }

        if (!is_null($parentPage->children)) {
            usort($parentPage->children, function (PicoPage $a, PicoPage $b) {
                if (self::isDirectory($a)) {
                    if (self::isDirectory($b)) {
                        return strcmp($a->meta->title, $b->meta->title);
                    } else {
                        return -1;
                    }
                }
                if (self::isDirectory($b)) {
                    return 1;
                }
                return strcmp($a->meta->title, $b->meta->title);
            });
        }

        return $parentPage;
    }

    private static function isDirectory(PicoPage $page): bool {
        return !isset($page->meta->kind) && str_ends_with($page->file, 'index.md');
    }
}
