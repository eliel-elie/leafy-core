# Logging System

A lightweight, flexible, and PSR-3 compatible logging system for PHP applications. This library provides a simple way to log messages with different severity levels, supporting file-based logging with rotation and customizable formatting.

## Features
- Supports all PSR-3 log levels (`emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`)
- File-based logging with automatic log rotation based on file size
- Configurable log directory, file size limits, and number of rotated files
- Customizable log message format with timestamp, level, message, and context
- Context support for adding additional metadata to log messages
- Singleton-based static interface for easy access across the application
- Extensible architecture for custom formatters and writers

## Installation

1. Copy the following PHP files into your project:
   - `Log.php`
   - `Logger.php`
   - `LogLevel.php`
   - `LogFormatter.php`
   - `FileLogWriter.php`

2. Ensure the `PATH_APP` constant is defined in your application, pointing to the base directory of your project. For example:
   ```php
   define('PATH_APP', __DIR__);
   ```

3. Make sure the log directory (default: `logs`) is writable by the application.

## Usage

### Basic Usage
Use the `Log` class statically to log messages at different levels:

```php
<?php

// Log an info message
Log::info('Application started');

// Log an error with context
Log::error('Database connection failed', ['host' => 'localhost', 'error' => 'Connection refused']);

// Log a warning
Log::warning('Cache is almost full', ['usage' => '90%']);
```

### Custom Configuration
Create a custom `Logger` instance with specific settings and set it as the singleton instance:

```php
<?php

$formatter = new LogFormatter(
    dateFormat: 'Y-m-d H:i:s.u', // Include microseconds in timestamp
    messageFormat: '[{datetime}] [{level}] {message} {context}' // Custom format
);

$writer = new FileLogWriter(
    logPath: 'custom_logs', // Custom log directory
    dateFormat: 'Y-m-d', // Log file name format
    maxFileSize: 5 * 1024 * 1024, // 5MB max file size
    maxFiles: 5 // Keep up to 5 rotated files
);

$logger = new Logger(
    formatter: $formatter,
    writer: $writer,
    context: ['app_version' => '1.0.0'] // Default context
);

Log::setInstance($logger);

// Now use the custom logger
Log::debug('Debug message', ['user_id' => 123]);
```

### Adding Context
Add contextual data to logs either globally or per message:

```php
<?php 

// Add global context
$logger = Log::getInstance()->withContext(['environment' => 'production']);
Log::setInstance($logger);

// Log with additional context
Log::notice('User logged in', ['user_id' => 456, 'ip' => '192.168.1.1']);
```

### Log File Location
Logs are stored in the directory specified by `logPath` (default: `logs` relative to `PATH_APP`). Log files are named `app-YYYY-MM-DD.log`, with rotated files suffixed with `-1`, `-2`, etc.

Example log file content:
```
[2025-06-12 10:13:45] INFO: Application started {}
[2025-06-12 10:14:00] ERROR: Database connection failed {"host":"localhost","error":"Connection refused"}
```

## Configuration Options

### FileLogWriter
- `logPath`: Directory to store log files (default: `logs`)
- `dateFormat`: Format for log file names (default: `Y-m-d`)
- `maxFileSize`: Maximum size of a log file in bytes (default: 10MB)
- `maxFiles`: Maximum number of rotated log files to keep (default: 10)

### LogFormatter
- `dateFormat`: Format for timestamps in log messages (default: `Y-m-d H:i:s`)
- `messageFormat`: Template for log messages (default: `[{datetime}] {level}: {message} {context}`)

## Log Levels
The system supports the following log levels (defined in `LogLevel.php`):
- `emergency`: System is unusable
- `alert`: Action must be taken immediately
- `critical`: Critical conditions
- `error`: Runtime errors
- `warning`: Exceptional occurrences that are not errors
- `notice`: Normal but significant events
- `info`: Interesting events
- `debug`: Detailed debug information

## License
This logging system is open-source and available under the MIT License.