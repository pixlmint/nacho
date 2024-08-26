<?php

namespace Nacho\Helpers\Log;

use DateTime;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Writes Logs to a file
 */
class Logger implements LoggerInterface, NachoLoggerInterface
{
    private LogWriterInterface $logWriter;
    private string $logFormat;
    private string $dateFormat;
    private DateTimeZone $utc;

    public function __construct(LogWriterInterface $logWriter, string $logFormat, string $dateFormat = 'Y-m-d H:i:s')
    {
        $this->logWriter = $logWriter;
        $this->logFormat = $logFormat;
        $this->dateFormat = $dateFormat;
        $this->utc = new DateTimeZone('UTC');
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = []): void
    {
        $date = new DateTime('now', $this->utc);
        $message = $this->formatMessage($level, $date, $message, $context);

        $this->logWriter->write($message);
    }

    private function formatMessage(string $level, DateTime $dt, string $message, array $context = []): string
    {
        $formatted = $dt->format($this->dateFormat);
        $logFormatKeys = ["{date}" => $formatted, "{level}" => $level, "{message}" => $message];
        $logMessage = $this->logFormat;
        foreach ($logFormatKeys as $key => $value) {
            $logMessage = str_replace($key, $value, $logMessage);
        }
        return $logMessage;
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(Level::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = []): void
    {
        $this->log(Level::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = []): void
    {
        $this->log(Level::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = []): void
    {
        $this->log(Level::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = []): void
    {
        $this->log(Level::WARNING, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = []): void
    {
        $this->log(Level::NOTICE, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = []): void
    {
        $this->log(Level::INFO, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = []): void
    {
        $this->log(Level::DEBUG, $message, $context);
    }
}
