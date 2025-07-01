<?php

namespace LeafyTech\Core\Support\Log;

class Logger
{
    private LogFormatter $formatter;
    private FileLogWriter $writer;
    private array $context;

    public function __construct(
        ?LogFormatter $formatter = null,
        ?FileLogWriter $writer   = null,
        array $context = []
    ) {
        $this->formatter = $formatter ?? new LogFormatter();
        $this->writer    = $writer ?? new FileLogWriter();
        $this->context   = $context;
    }

    public function withContext(array $context): self
    {
        $newLogger          = clone $this;
        $newLogger->context = array_merge($this->context, $context);
        return $newLogger;
    }

    public function log(string $level, string $message, array $context = []): void
    {
        if (!LogLevel::isValid($level)) {
            throw new \InvalidArgumentException("Invalid log level: {$level}");
        }

        $mergedContext    = array_merge($this->context, $context);
        $formattedMessage = $this->formatter->format($level, $message, $mergedContext);
        $this->writer->write($level, $formattedMessage);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}