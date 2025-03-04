<?php

namespace Nacho\Helpers;

use Exception;
use Nacho\Contracts\PageHandler;
use Nacho\Contracts\PageManagerInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Hooks\NachoAnchors\PostHandleUpdateAnchor;
use Nacho\Models\PicoMeta;
use Nacho\Models\PicoPage;
use Psr\Log\LoggerInterface;

class PageManager implements PageManagerInterface
{
    /**
     * Set this flag to True if you want an additional 'children' index when getting pages
     * Will increase execution time considerably so use cautiously
     */
    public static bool $INCLUDE_PAGE_TREE = false;

    /** @var array|PicoPage[] $pages */
    private array $pages;

    /** @var array|PicoPage[] $pageTree */
    private array $pageTree = [];

    private FileHelper $fileHelper;
    private UserHandlerInterface $userHandler;
    private LoggerInterface $logger;
    private HookHandler $hookHandler;
    private array $movedPages;
    private PageFinder $pageFinder;
    private MarkdownPageHandler $markdownPageHandler;
    private AlternativeContentPageHandler $alternativeContentPageHandler;

    public function __construct(FileHelper $fileHelper, UserHandlerInterface $userHandler, LoggerInterface $logger, HookHandler $hookHandler, PageFinder $pageFinder, MarkdownPageHandler $markdownPageHandler, AlternativeContentPageHandler $alternativeContentPageHandler)
    {
        $this->pages = [];
        $this->fileHelper = $fileHelper;
        $this->userHandler = $userHandler;
        $this->logger = $logger;
        $this->hookHandler = $hookHandler;
        $this->pageFinder = $pageFinder;
        $this->markdownPageHandler = $markdownPageHandler;
        $this->alternativeContentPageHandler = $alternativeContentPageHandler;
    }

    /**
     * @return array|PicoPage[]
     */
    public function getPages(): array
    {
        if (!$this->pages) {
            $this->readPages();
        }

        return $this->pages;
    }

    /**
     * @return array|PicoPage[]
     */
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
        $pageHandler = $this->getPageHandler($page);
        $this->logger->debug(sprintf('Rendering page id %s with renderer %s', $page->id, get_class($pageHandler)));
        return $pageHandler->renderPage();
    }

    private function getPageHandler(PicoPage $page): PageHandler
    {
        $pageHandler = $page->meta->renderer !== null ? $this->alternativeContentPageHandler : $this->markdownPageHandler;

        $pageHandler->setPage($page);
        return $pageHandler;
    }

    /**
     * Returns null on error, or else the new entry id
     */
    public function move(string $id, string $targetFolder): ?string
    {
        $page = $this->getPage($id);
        $parent = $this->getPage($targetFolder);
        $page->meta->parentPath = $targetFolder;
        $this->editPage($id, $page->raw_content, $page->meta->toArray());

        $targetFolder = str_replace('/index.md', '', $parent->file);
        $filenameSpl = explode('/', $page->file);
        $filename = array_pop($filenameSpl);
        $newPath = $targetFolder . '/' . $filename;

        $success = $this->fileHelper->move($page->file, $newPath);

        if (!$success) {
            return null;
        }

        $this->movedPages[$id] = $targetFolder;

        $this->hookHandler->executeHook(PostHandleUpdateAnchor::getName(), ['entry' => $page]);

        return $newPath;
    }

    # TODO: This should use the FileHelper instead
    public function delete(string $id): bool
    {
        $success = true;
        $page = $this->getPage($id);

        if (!$page) {
            return $success;
        }

        $handler = $this->getPageHandler($page);
        $handler->handleDelete();

        if (is_file($page->file)) {
            $success = unlink($page->file);
        }
        if ($success) {
            $this->logger->debug('Deleted page with id ' . $id);
            $this->readPages();
        } else {
            $this->logger->error(sprintf('Error deleting page with id %s', $id));
        }

        $this->hookHandler->executeHook(PostHandleUpdateAnchor::getName(), ['entry' => $page]);

        return $success;
    }

    /**
     * @param string $url        the URL of the page to edit
     * @param string $newContent the new content
     * @param array  $newMeta    New metadata to be merged with the existing meta data
     *
     * @return bool True if the updated page was successfully stored
     * @throws Exception If the page does not exist
     */
    public function editPage(string $url, string $newContent, array $newMeta): bool
    {
        $page = $this->getPage($url);
        if (!$page) {
            throw new Exception("{$url} does not exist");
        }
        $oldMeta = $page->meta->toArray();
        $newMeta = array_merge($oldMeta, $newMeta);
        // Fallback for older entries that don't yet possess the owner info
        if (!key_exists('owner', $newMeta) || !$newMeta['owner']) {
            $newMeta['owner'] = $this->userHandler->getCurrentUser()->getUsername();
        }
        // Fallback for older entries that don't yet possess the dateCreated info
        if (key_exists('date', $newMeta) && key_exists('time', $newMeta)) {
            $newMeta['dateCreated'] = $newMeta['date'] . ' ' . $newMeta['time'];
            unset($newMeta['date'], $newMeta['time']);
        }
        $newMeta['dateUpdated'] = date('Y-m-d H:i:s');
        $newPage = $page->duplicate();
        if ($newContent) {
            $newPage->raw_content = $newContent;
        }
        $newPage->meta = new PicoMeta($newMeta);

        $handler = $this->getPageHandler($newPage);
        $newPage = $handler->handleUpdate($url, $newContent, $newMeta);

        $success = $this->fileHelper->storePage($newPage);
        if ($success) {
            $this->logger->debug('Edited page with id ' . $newPage->id);
        } else {
            $this->logger->error(sprintf('Error editing page with id %s at path %s', $newPage->id, $newPage->file));
        }

        return $success;
    }

    public function create(string $parentFolder, string $title, bool $isFolder = false): ?PicoPage
    {
        $page = $this->getPage($parentFolder);

        if (!$page && $parentFolder !== '/') {
            throw new Exception('Unable to find this page');
        }

        $newPage = new PicoPage();
        $newPage->raw_content = 'Write Some Content';
        $meta = new PicoMeta();
        $meta->title = $title;
        $meta->dateCreated = date('Y-m-d H:i:s');
        $meta->dateUpdated = date('Y-m-d H:i:s');
        $meta->parentPath = $parentFolder;
        $newPage->meta = $meta;

        $contentDir = self::getContentDir();

        $parentDir = preg_replace('/index.md$/', '', $parentFolder);
        if (!str_ends_with($parentDir, '/')) {
            $parentDir .= '/';
        }
        if ($isFolder) {
            $newDirectory = FileNameHelper::generateFileNameFromTitle($title);
            $directory = $contentDir . $parentDir . $newDirectory;
            mkdir($directory);
            $file = $directory . DIRECTORY_SEPARATOR . 'index.md';
            $newPage->id = $parentDir . $newDirectory;
        } else {
            $fileName = FileNameHelper::generateFileNameFromTitle($title) . '.md';
            $file = $contentDir . $parentDir . $fileName;
            $fileName = preg_replace('/\.md$/', '', $fileName);
            $newPage->id = $parentDir . $fileName;
        }

        $newPage->file = $file;

        $success = $this->fileHelper->storePage($newPage);

        $this->hookHandler->executeHook(PostHandleUpdateAnchor::getName(), ['entry' => $page]);

        if ($success) {
            $this->logger->debug('Created Page with id ' . $newPage->id);
            $this->readPages();
            return $newPage;
        }

        $this->logger->error(sprintf('Error creating Page with id %s at file %s', $newPage->id, $newPage->file));
        return null;
    }

    public function readPages(): void
    {
        if (!self::rootPageExists()) {
            $this->createRootPage();
        }
        $this->pages = $this->pageFinder->readPages(self::getContentDir());

        if (self::$INCLUDE_PAGE_TREE) {
            self::$INCLUDE_PAGE_TREE = false;
            $rootPage = $this->getPage('/');
            self::$INCLUDE_PAGE_TREE = true;
            if ($rootPage) {
                $this->pageTree = ['/' => $this->pageFinder->findChildPages('/', $rootPage, $this->pages)];
            } else {
                throw new Exception('Unable to find root page');
            }
        }
    }

    /**
     * Creates an index.md file in the [content_dir]/ directory.
     * If that file already exists the method just exits.
     * @return void
     * @throws Exception if page could not be created
     */
    public function createRootPage(): void
    {
        $this->logger->info('Creating root Page');
        if (self::rootPageExists()) {
            return;
        }

        $rootFilePath = self::getContentDir() . '/index.md';
        $fileContent = "---\ntitle: Home\n---\nWelcome Home";
        $this->fileHelper->storeFileContents($rootFilePath, $fileContent);
    }

    private static function rootPageExists(): bool
    {
        $rootFilePath = self::getContentDir() . '/index.md';

        return file_exists($rootFilePath);
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
}
