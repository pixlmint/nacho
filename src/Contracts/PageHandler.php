<?php

namespace Nacho\Contracts;

use Nacho\Models\PicoPage;

interface PageHandler
{
    public function renderPage(): string;

    public function handleUpdate(string $url, string $newContent, array $newMeta): PicoPage;

    public function handleDelete(): void;
}