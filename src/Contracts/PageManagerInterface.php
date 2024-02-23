<?php

namespace Nacho\Contracts;

use Nacho\Models\PicoPage;

interface PageManagerInterface
{
    public function getPages(): array;

    public function getPageTree(): array;

    public function getPage(string $url): ?PicoPage;

    public function renderPage(PicoPage $page): string;

    public function delete(string $id): bool;

    public function move(string $id, string $targetFolder): bool;

    public function editPage(string $url, string $newContent, array $newMeta): bool;

    public function create(string $parentFolder, string $title, bool $isFolder = false): ?PicoPage;

    public function readPages(): void;

    public static function prepareFileContent(string $rawContent): string|array|null;

    public static function getContentDir(): string;

    public static function isSubPath(string $path, string $parentPath): bool;

    public function findChildPages(string $id, PicoPage &$parentPage, array $pages): PicoPage;

}