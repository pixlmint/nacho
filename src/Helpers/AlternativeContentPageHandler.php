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
    private PdfHelper $pdfHelper;
    private JupyterNotebookHelper $jupyterNotebookHelper;

    public function __construct(Request $request, PdfHelper $pdfHelper, JupyterNotebookHelper $jupyterNotebookHelper)
    {
        $this->request = $request;
        $this->pdfHelper = $pdfHelper;
        $this->jupyterNotebookHelper = $jupyterNotebookHelper;
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
            if (isset($uploadedFile['name'])) {
                $this->deleteOldFile();
                $this->storeNewFile($uploadedFile);
            } else if (isset($uploadedFile['content'])) {
                $this->replaceFileContent($uploadedFile['content']);
            } else {
                // This is a strange file...
            }
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
        if ($this->page->meta->renderer === 'ipynb') {
            $mdPageHandler = Nacho::$container->get(MarkdownPageHandler::class);

            $mdPageHandler->setPage($this->page);
            return $mdPageHandler->renderPage();
        } else {
            return 'rendered content here';
        }
    }

    private function getUploadedFile(): array
    {
        $uploadedFiles = $this->request->getFiles();

        if (isset($uploadedFiles['alternative_content'])) {
            $uploadedFile = $uploadedFiles['alternative_content'];

            if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
                if ($uploadedFile['error'] === UPLOAD_ERR_INI_SIZE) {
                    $maxSize = ini_get('upload_max_filesize');
                    throw new Exception("Uploaded file exceeds size limit (max: $maxSize)");
                }
                throw new Exception("File upload error: " . $uploadedFile['error']);
            }

            return $uploadedFile;
        } else if ($this->request->getBody()->has('alternative_content_raw')) {
            return ['content' => $this->request->getBody()->get('alternative_content_raw')];
        } else {
            return [];
        }
    }

    public function getAbsoluteFilePath(): string
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

    private function replaceFileContent(string $content): void
    {
        $page = $this->page;
        $entryPath = PageManager::getContentDir() . DIRECTORY_SEPARATOR . $page->meta->parentPath . DIRECTORY_SEPARATOR . $page->meta->alternative_content;
        file_put_contents($entryPath, $content);

        $this->handleExtractContent($entryPath);
    }

    private function storeNewFile(array $uploadedFile): void
    {
        $page = $this->page;
        $newFilename = $uploadedFile['name'];
        $entryPath = PageManager::getContentDir() . DIRECTORY_SEPARATOR . $page->meta->parentPath . DIRECTORY_SEPARATOR . $newFilename;

        $uploadSuccess = file_put_contents($entryPath, file_get_contents($uploadedFile['tmp_name']));

        if ($uploadSuccess === false) {
            throw new Exception("Failed to write the new file to {$entryPath}");
        }

        $this->handleExtractContent($entryPath);

        $page->meta->alternative_content = $newFilename;
    }

    private function handleExtractContent(string $newFilePath)
    {
        $page = $this->page;
        if ($page->meta->renderer === 'pdf') {
            $page->raw_content = $this->pdfHelper->getContent($newFilePath);
        } elseif ($page->meta->renderer === 'ipynb') {
            $page->raw_content = $this->jupyterNotebookHelper->getContent($newFilePath);
        }
    }
}
