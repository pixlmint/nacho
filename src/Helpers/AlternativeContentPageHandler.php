<?php

namespace Nacho\Helpers;

use Exception;
use Nacho\Contracts\AlternativeContentType;
use Nacho\Contracts\PageHandler;
use Nacho\Nacho;
use Nacho\Exceptions\BadContentRendererException;
use Nacho\Models\PicoPage;
use Nacho\Models\Request;

class AlternativeContentPageHandler implements PageHandler
{
    private PicoPage $page;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function setPage(PicoPage $page): void
    {
        $this->page = $page;
        if (!$this->isValidContentType()) {
            throw new BadContentRendererException(printf('Content Type %s is not supported', $page->meta->renderer));
        }
    }

    private function isValidContentType(): bool
    {
        /** @var array|string[]|AlternativeContentType[] $enabledContentTypes */
        $enabledContentTypes = Nacho::$container->get(ConfigurationContainer::class)->getAlternativeContentHandlers();

        foreach ($enabledContentTypes as $contentType) {
            if ($contentType::rendererValue() === $this->page->meta->renderer) {
                return true;
            }
        }

        return false;
    }

    public function handleUpdate(string $url, string $newContent, array $newMeta): PicoPage
    {
        $uploadedFile = $this->getUploadedFile();

        if ($uploadedFile) {
            $this->deleteOldFile();
            $this->storeNewFile($uploadedFile);
        }

        return $this->page;
    }

    public function handleDelete(): void
    {
        $contentPath = $this->getAbsoluteFilePath();
        if (is_file($contentPath)) {
            unlink($contentPath);
        }
    }

    public function renderPage(): string
    {
        $pdfContent = file_get_contents($this->getAbsoluteFilePath());

        return base64_encode($pdfContent);
    }

    private function getUploadedFile(): array
    {
        $uploadedFiles = $this->request->getFiles();

        if (!isset($uploadedFiles['alternative_content'])) {
            return [];
        }

        $uploadedFile = $uploadedFiles['alternative_content'];

        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $uploadedFile['error']);
        }

        return $uploadedFile;
    }

    private function getAbsoluteFilePath(): string
    {
        return PageManager::getContentDir() . $this->page->meta->parentPath . DIRECTORY_SEPARATOR . $this->page->meta->alternative_content;
    }

    private function deleteOldFile(): void
    {
        $page = $this->page;
        if (!isset($page->meta->alternative_content)) {
            return;
        }
        $oldFilePath = $this->getAbsoluteFilePath();
        if (file_exists($oldFilePath) && !unlink($oldFilePath)) {
            throw new Exception("Failed to delete the old file at {$oldFilePath}");
        }
    }

    private function storeNewFile(array $uploadedFile): void
    {
        $page = $this->page;
        $newFilename = $uploadedFile['name'];
        $entryPath = PageManager::getContentDir() . DIRECTORY_SEPARATOR . $page->meta->parentPath . DIRECTORY_SEPARATOR . $newFilename;

        if (file_put_contents($entryPath, file_get_contents($uploadedFile['tmp_name'])) === false) {
            throw new Exception("Failed to write the new file to {$entryPath}");
        }

        $page->meta->alternative_content = $newFilename;
    }
}