<?php

namespace LeafyTech\Core\Support\Log;

class FileLogWriter
{
    private string $logPath;
    private string $dateFormat;
    private int $maxFileSize;
    private int $maxFiles;

    public function __construct(
        string $logPath     = 'logs',
        string $dateFormat  = 'Y-m-d',
        int $maxFileSize    = 10 * 1024 * 1024,
        int $maxFiles       = 10
    ) {
        $this->logPath      = app()->basePath(rtrim($logPath));
        $this->dateFormat   = $dateFormat;
        $this->maxFileSize  = $maxFileSize;
        $this->maxFiles     = $maxFiles;

        $this->createLogDirectory();
    }

    public function write(string $level, string $formattedMessage): void
    {
        $filename = $this->getLogFilename($level);
        $filepath = $this->logPath . '/' . $filename;

        if (file_exists($filepath) && filesize($filepath) > $this->maxFileSize) {
            $this->rotateLogFile($filepath);
        }

        file_put_contents($filepath, $formattedMessage, FILE_APPEND | LOCK_EX);
    }

    private function getLogFilename(string $level): string
    {
        $date = date($this->dateFormat);
        return "app-{$date}.log";
    }

    private function createLogDirectory(): void
    {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    private function rotateLogFile(string $filepath): void
    {
        $pathInfo   = pathinfo($filepath);
        $baseName   = $pathInfo['filename'];
        $extension  = $pathInfo['extension'];
        $directory  = $pathInfo['dirname'];

        for ($i = $this->maxFiles - 1; $i > 0; $i--) {
            $oldFile = "{$directory}/{$baseName}-{$i}.{$extension}";
            $newFile = "{$directory}/{$baseName}-" . ($i + 1) . ".{$extension}";

            if (file_exists($oldFile)) {
                if ($i == $this->maxFiles - 1) {
                    unlink($oldFile);
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }

        rename($filepath, "{$directory}/{$baseName}-1.{$extension}");
    }
}