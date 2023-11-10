<?php

namespace Nacho\Helpers\Log;

use Psr\Log\LoggerInterface;
use Stringable;

/**
 * Writes Logs to a file
 */
class Logger implements LoggerInterface, NachoLoggerInterface
{
    private string $logFile;
    private string $dateFormat;
    private LogWriterInterface $logWriter;

    public function __construct(LogWriterInterface $logWriter)
    {
        $this->logFile = $logFile;
        $this->dateFormat = $dateFormat;
        $this->logWriter = $logWriter;
    }

    /**
     * @inheritDoc
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        if (!$level instanceof Level) {
            throw new \InvalidArgumentException('Invalid level');
        }
        $this->logWriter->write($message, $level, implode(';', $context));
    }

    /**
     * @inheritDoc
     */
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->log(Level::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->log(Level::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->log(Level::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->log(Level::ERROR, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->log(Level::WARNING, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->log(Level::NOTICE, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->log(Level::INFO, $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->log(Level::DEBUG, $message, $context);
    }
}