<?php

namespace Nacho\Helpers;

use Exception;
use Nacho\Contracts\PageHandler;
use Nacho\Contracts\SingletonInterface;
use Nacho\Core;
use Nacho\Models\PicoMeta;
use Nacho\Models\PicoPage;
use Symfony\Component\Yaml\Exception\ParseException;

class PageManager implements SingletonInterface
{
    /**
     * Set this flag to True if you want an additional 'children' index when getting pages
     * Will increase execution time considerably so use cautiously
     */
    public static bool $INCLUDE_PAGE_TREE = true;

    /** @var array|PicoPage[] $pages */
    private array $pages;

    /** @var array|PicoPage[] $pageTree */
    private array $pageTree = [];

    private MetaHelper $metaHelper;
    private PageSecurityHelper $pageSecurityHelper;
    private FileHelper $fileHelper;

    private static SingletonInterface|PageManager|null $instance = null;

    public function __construct()
    {
        $this->pages = [];
        $this->metaHelper = new MetaHelper();
        $this->pageSecurityHelper = new PageSecurityHelper();
        $this->fileHelper = new FileHelper();
    }

    public static function getInstance(): SingletonInterface|PageManager
    {
        if (!self::$instance) {
            self::$instance = new PageManager();
        }

        return self::$instance;
    }

    public function getPages(): array
    {
        if (!$this->pages) {
            $this->readPages();
        }

        return $this->pages;
    }

    public function getPageTree(): array
    {
        if (!self::$INCLUDE_PAGE_TREE) {
            throw new Exception('Page tree is not enabled. Set INCLUDE_PAGE_TREE to true in PageManager.php');
        }

        if (!$this->pageTree) {
            $this->readPages();
        }

        return $this->pageTree;
    }

    public function getPage(string $url): ?PicoPage
    {
        if (self::$INCLUDE_PAGE_TREE) {
            $pages = $this->getPageTree();
            return $this->getPageFromTree($pages, $url);
        } else {
            $pages = $this->getPages();
            return $this->getPageFromFlat($pages, $url);
        }
    }

    private function getPageFromTree(array $pages, string $url): ?PicoPage
    {
        foreach ($pages as $page) {
            if (!isset($page->id)) {
                continue;
            }
            if ($page->id === $url) {
                return $page;
            }
            if (isset($page->children)) {
                $childPage = $this->getPageFromTree($page->children, $url);
                if ($childPage) {
                    return $childPage;
                }
            }
        }

        return null;
    }

    private function getPageFromFlat(array $pages, string $url): ?PicoPage
    {
        foreach ($pages as $page) {
            if (!isset($page->id)) {
                continue;
            }
            if ($page->id === $url) {
                return $page;
            }
        }

        return null;
    }

    public function renderPage(PicoPage $page): string
    {
        return $this->getPageHandler($page)->renderPage();
    }

    private function getPageHandler(PicoPage $page): PageHandler
    {
        if (isset($page->meta->renderer)) {
            return new AlternativeContentPageHandler($page);
        } else {
            return new MarkdownPageHandler($page);
        }
    }

    public function delete(string $id): bool
    {
        $page = $this->getPage($id);

        if (!$page) {
            return true;
        }

        $handler = $this->getPageHandler($page);
        $handler->handleDelete();

        if (is_file($page->file)) {
            return unlink($page->file);
        }

        return true;
    }

    public function editPage(string $url, string $newContent, array $newMeta): bool
    {
        $page = $this->getPage($url);
        if (!$page) {
            throw new Exception("{$url} does not exist");
        }
        $oldMeta = (array)$page->meta;
        $newMeta = array_merge($oldMeta, $newMeta);
        // Fallback for older entries that don't yet possess the owner info
        if (!$newMeta['owner']) {
            $newMeta['owner'] = Core::getUserHandler()->getCurrentUser()->getUsername();
        }

        $newPage = $page->duplicate();
        if ($newContent) {
            $newPage->raw_content = $newContent;
        }
        $newPage->meta = new PicoMeta($newMeta);

        $handler = $this->getPageHandler($newPage);
        $newPage = $handler->handleUpdate($url, $newContent, $newMeta);

        return $this->fileHelper->storePage($newPage);
    }

    public function create(string $parentFolder, string $title, bool $isFolder = false): ?PicoPage
    {
        $page = $this->getPage($parentFolder);

        if (!$page) {
            throw new Exception('Unable to find this page');
        }

        $newPage = new PicoPage();
        $newPage->raw_content = 'Write Some Content';
        $meta = new PicoMeta();
        $meta->title = $title;
        $meta->date = date('Y-m-d');
        $meta->time = date('h:i:s');
        $newPage->meta = $meta;

        $contentDir = self::getContentDir();

        $parentDir = preg_replace('/index.md$/', '', $parentFolder);
        if (!str_ends_with($parentDir, '/')) {
            $parentDir .= '/';
        }
        if ($isFolder) {
            // TODO: Folder names that contain a space don't work
            $directory = $contentDir . $parentDir . $title;
            mkdir($directory);
            $file = $directory . DIRECTORY_SEPARATOR . 'index.md';
            $newPage->id = $parentDir . $title;
        } else {
            $fileName = FileNameHelper::generateFileNameFromTitle($meta->title);
            $file = $contentDir . $parentDir . $fileName;
            $fileName = preg_replace('/\.md$/', '', $fileName);
            $newPage->id = $parentDir . $fileName;
        }

        $newPage->file = $file;

        $success = $this->fileHelper->storePage($newPage);

        if ($success)
            return $newPage;
        return null;
    }

    public function readPages(): void
    {
        $contentDir = self::getContentDir();

        $this->pages = array();
        $files = $this->fileHelper->getFiles($contentDir);
        foreach ($files as $i => $file) {
            $id = substr($file, strlen($contentDir), -3);

            // skip inaccessible pages (e.g. drop "sub.md" if "sub/index.md" exists) by default
            $conflictFile = $contentDir . $id . '/index.md';
            $skipFile = in_array($conflictFile, $files, true) || null;

            if ($skipFile) {
                continue;
            }

            if (str_ends_with($id, '/index')) {
                $id = substr($id, 0, -6);
            }
            if (!$id) {
                $id = '/';
            }

            $url = UrlHelper::getPageUrl($id);
            $rawMarkdown = FileHelper::loadFileContent($file);
            $rawContent = $this->prepareFileContent($rawMarkdown);

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
                $this->pages[$id] = $page;
            }
        }
        if (self::$INCLUDE_PAGE_TREE) {
            $this->pageTree = [];
            $rootPage = $this->getPage('/');
            $this->pageTree = ['/' => $this->findChildPages('/', $rootPage, $this->pages)];
        }
    }

    /**
     * @param string $rawContent
     *
     * @return string|array|null
     */
    public static function prepareFileContent(string $rawContent): string|array|null
    {
        // remove meta header
        $metaHeaderPattern = "/^(?:\xEF\xBB\xBF)?(\/(\*)|---)[[:blank:]]*(?:\r)?\n"
            . "(?:(.*?)(?:\r)?\n)?(?(2)\*\/|---)[[:blank:]]*(?:(?:\r)?\n|$)/s";
        return preg_replace($metaHeaderPattern, '', $rawContent, 1);
    }

    public static function getContentDir(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/content';
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

        return $parentPage;
    }
}