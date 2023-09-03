<?php

namespace Nacho\Helpers;

use Exception;
use Nacho\Contracts\AlternativeContentType;
use Nacho\Contracts\PageHandler;
use Nacho\Exceptions\BadContentRendererException;
use Nacho\Models\PicoPage;
use Nacho\Models\Request;

class AlternativeContentPageHandler implements PageHandler
{
    private PicoPage $page;

    public function __construct(PicoPage $page)
    {
        $this->page = $page;
        if (!$this->isValidContentType()) {
            throw new BadContentRendererException(printf('Content Type %s is not supported', $page->meta->renderer));
        }
    }

    private function isValidContentType()
    {
        /** @var array|string[]|AlternativeContentType[] $enabledContentTypes */
        $enabledContentTypes = ConfigurationHelper::getInstance()->getAlternativeContentHandlers();

        foreach ($enabledContentTypes as $contentType) {
            if ($contentType::rendererValue() === $this->page->meta->renderer) {
                return true;
            }
        }

        return false;
    }

    public function handleUpdate(string $url, string $newContent, array $newMeta): PicoPage
    {
        $uploadedFile = $this->getUploadedFileOrThrow();

        $this->deleteOldFile();
        $this->storeNewFile($uploadedFile);

        return $this->page;
    }

    public function handleDelete(): void
    {
        // TODO: Implement handleDelete() method.
    }

    public function renderPage(): string
    {
        $contentPath = $this->page->meta->parentPath . '/' . $this->page->meta->alternative_content;
        $pdfContent = file_get_contents(PageManager::getContentDir() . $contentPath);

        return base64_encode($pdfContent);
    }

    private function getUploadedFileOrThrow(): array
    {
        $request = Request::getInstance();
        $uploadedFiles = $request->getFiles();

        if (!isset($uploadedFiles['alternate_content'])) {
            throw new Exception("No uploaded file with key 'alternate_content' found.");
        }

        $uploadedFile = $uploadedFiles['alternate_content'];

        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $uploadedFile['error']);
        }

        return $uploadedFile;
    }

    private function deleteOldFile(): void
    {
        $page = $this->page;
        $oldFilePath = PageManager::getContentDir() . DIRECTORY_SEPARATOR . $page->meta->parentPath . DIRECTORY_SEPARATOR . $page->meta->alternative_content;
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