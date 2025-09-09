<?php

namespace LeafyTech\Core\Support\Log;

class FileLogWriter
{
    private string $logPath;
    private string $dateFormat;
    private int $maxFileSize;
    private int $maxFiles;

    public function __construct(
        string $logPath     = '',
        string $dateFormat  = 'Y-m-d',
        int $maxFileSize    = 10 * 1024 * 1024,
        int $maxFiles       = 10
    ) {

        $logPath = ! empty($logPath) ? rtrim($logPath, '/') : app()->basePath(rtrim('storage/logs'));;

        $this->logPath      = $logPath;
        $this->dateFormat   = $dateFormat;
        $this->maxFileSize  = $maxFileSize;
        $this->maxFiles     = $maxFiles;

        $this->createLogDirectory();
    }

    public function write(string $level, string $formattedMessage): void
    {
        $filename = $this->getLogFilename($level);
        $filepath = $this->logPath . '/' . $filename;

        $this->cleanExcessLogFiles();

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

    private function cleanExcessLogFiles(): void
    {
        $directory = $this->logPath;
        $baseName  = "app-";

        $pattern   = "{$directory}/{$baseName}*.log";
        $logFiles  = glob($pattern);

        usort($logFiles, function ($a, $b) {
            preg_match('/-(\d+)\.log$/', $a, $aMatch);
            preg_match('/-(\d+)\.log$/', $b, $bMatch);
            $aNum = $aMatch ? (int)$aMatch[1] : 0;
            $bNum = $bMatch ? (int)$bMatch[1] : 0;

            return $aNum <=> $bNum;
        });

        while (count($logFiles) > $this->maxFiles) {
            $oldestFile = array_shift($logFiles);

            if (file_exists($oldestFile)) {
                unlink($oldestFile);
            }
        }
    }

    private function rotateLogFile($filepath): void
    {
        $pathInfo  = pathinfo($filepath);
        $baseName  = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $directory = $pathInfo['dirname'];

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