<?php

namespace LeafyTech\Core\Handlers;

use LeafyTech\Core\Support\Log\Log;
use Throwable;

class ErrorHandler
{
    public function register(): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $context = ['file' => $file, 'line' => $line];

        switch ($severity) {
            case E_ERROR:
            case E_USER_ERROR:
                Log::error($message, $context);
                break;

            case E_WARNING:
            case E_USER_WARNING:
                Log::warning($message, $context);
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                Log::notice($message, $context);
                break;

            default:
                Log::info($message, $context);
                break;
        }

        return false;
    }

    public function handleException(Throwable $exception): void
    {
        Log::critical(
            "Uncaught Exception: " . $exception->getMessage(),
            [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]
        );

        http_response_code(500);

        echo "<h1>500 - Internal Server Error</h1><p>An unexpected error occurred. Please try again later.</p>";
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            Log::error(
                "Fatal Error: " . $error['message'],
                [
                    'file' => $error['file'],
                    'line' => $error['line'],
                ]
            );
        }
    }
}
