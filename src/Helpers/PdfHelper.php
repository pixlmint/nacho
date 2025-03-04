<?php

namespace Nacho\Helpers;

use Nacho\Nacho;
use PixlMint\CMS\Helpers\Stopwatch;
use Psr\Log\LoggerInterface;
use Spatie\PdfToText\Pdf;

class PdfHelper
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getContent(string $pdfPath)
    {
        return $this->getPdfContent($pdfPath, [Pdf::class, 'getText']);
    }

    private function getPdfContent(string $pdfPath, callable $parser): string
    {
        if (Nacho::$container->get('debug')) {
            $initialMemoryUsage = memory_get_usage();
            $timer = Stopwatch::startNew();
            $pdfContent = call_user_func($parser, $pdfPath);
            $duration = $timer->stop();
            $readPdfMemoryUsage = memory_get_usage() - $initialMemoryUsage;
            $this->logger->debug(sprintf("Done indexing %s within %fs, using %d bytes", $pdfPath, $duration, $readPdfMemoryUsage));
            return $pdfContent;
        } else {
            return call_user_func($parser, $pdfPath);
        }
    }
}
