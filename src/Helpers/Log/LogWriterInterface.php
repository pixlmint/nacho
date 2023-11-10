<?php

namespace Nacho\Helpers\Log;

/**
 * The Interface for any Class that handles where logs get written to
 * For example a DB Log writer would store logs in the database.
 */
interface LogWriterInterface
{
    /**
     * Write a log message to the log writer
     *
     * @param string $message
     * @param Level  $level
     * @param string $context
     *
     * @return void
     */
    public function write(string $message): void;

    /**
     * Opens the connection to the log endpoint (file/ db)
     * @return void
     */
    public function open(): void;

    /**
     * Closes the connection
     * @return void
     */
    public function close(): void;
}