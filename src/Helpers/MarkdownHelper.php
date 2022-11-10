<?php

namespace Nacho\Helpers;

use Exception;
use Nacho\Models\PicoMeta;
use Nacho\Models\PicoPage;
use Parsedown;
use Symfony\Component\Yaml\Exception\ParseException;

class MarkdownHelper
{
    /** @var array|PicoPage[] $pages */
    private array $pages;
    private Parsedown $mdParser;
    private MetaHelper $metaHelper;

    public function __construct()
    {
        $this->pages = [];
        $this->mdParser = new Parsedown();
        $this->metaHelper = new MetaHelper();
    }

    public function clearPages(): void
    {
        $this->pages = [];
    }

    public function getFiles($directory): array
    {
        $directory = rtrim($directory, '/');
        $fileExtensionLength = strlen('.md');
        $result = [];

        $files = scandir($directory);
        if ($files !== false) {
            foreach ($files as $file) {
                // exclude hidden files/dirs starting with a .; this also excludes the special dirs . and ..
                // exclude files ending with a ~ (vim/nano backup) or # (emacs backup)
                if (($file[0] === '.') || in_array(substr($file, -1), array('~', '#'), true)) {
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
     * @return array|PicoPage[]
     */
    public function getPages(): array
    {
        if (!$this->pages) {
            $this->readPages();
        }

        return $this->pages;
    }

    public function getPage(string $url): ?PicoPage
    {
        $pages = $this->getPages();
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
        if (!isset($page->raw_content)) {
            return '';
        }

        $content = $this->prepareFileContent($page->raw_content);
        $page->content = $this->mdParser->parse($content);

        return $page->content;
    }

    /**
     * @param string $rawContent
     *
     * @return mixed
     */
    public function prepareFileContent(string $rawContent)
    {
        // remove meta header
        $metaHeaderPattern = "/^(?:\xEF\xBB\xBF)?(\/(\*)|---)[[:blank:]]*(?:\r)?\n"
            . "(?:(.*?)(?:\r)?\n)?(?(2)\*\/|---)[[:blank:]]*(?:(?:\r)?\n|$)/s";
        return preg_replace($metaHeaderPattern, '', $rawContent, 1);
    }

    /**
     * @param string $url
     * @param string $newContent
     * @param array $newMeta
     * @return void
     * @throws Exception
     */
    public function editPage(string $url, string $newContent, array $newMeta): bool
    {
        $page = $this->getPage($url);
        if (!$page) {
            throw new Exception("${url} does not exist");
        }
        $oldMeta = (array)$page->meta;
        $newMeta = array_merge($oldMeta, $newMeta);
        $newPage = $page->duplicate();
        if ($newContent) {
            $newPage->raw_content = $newContent;
        }
        $newPage->meta = new PicoMeta($newMeta);
        return $this->storePage($newPage);
    }

    /**
     * @param PicoPage $page
     * @return bool success
     */
    public function storePage(PicoPage $page): bool
    {
        $pageContent = MetaHelper::createMetaString((array)$page->meta) . $page->raw_content;
        if (!$pageContent || !$page->id || !$page->file) {
            return false;
        }
        $filePath = $page->file;
        return file_put_contents($filePath, $pageContent);
    }

    private static function getContentDir(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/content';
    }

    public function readPages()
    {
        $contentDir = self::getContentDir();

        $this->pages = array();
        $files = $this->getFiles($contentDir);
        foreach ($files as $i => $file) {
            $id = substr($file, strlen($contentDir), -3);

            // skip inaccessible pages (e.g. drop "sub.md" if "sub/index.md" exists) by default
            $conflictFile = $contentDir . $id . '/index.md';
            $skipFile = in_array($conflictFile, $files, true) ?: null;

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
            $rawMarkdown = self::loadFileContent($file);
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

            unset($rawContent, $rawMarkdown, $meta);

            $this->pages[$id] = $page;
        }
    }

    protected static function implode_recursive(array $arr, string $separator = ''): string
    {
        $ret = '';
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $ret .= $separator . $key . ': ' . self::implode_recursive($value, $separator);
            } else {
                $ret .= $separator . $key . ': ' . $value;
            }
        }

        return $ret;
    }

    public static function createMetaString(array $meta): string
    {
        return "---" . self::implode_recursive($meta, "\n") . "\n---\n";
    }

    protected static function loadFileContent($file)
    {
        return file_get_contents($file);
    }
}