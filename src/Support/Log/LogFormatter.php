<?php

namespace LeafyTech\Core\Support\Log;

class LogFormatter
{
    private string $dateFormat;

    private string $messageFormat;

    public function __construct(
        string $dateFormat      = 'Y-m-d H:i:s',
        string $messageFormat   = '[{datetime}] {level}: {message} {context}'
    ) {
        $this->dateFormat    = $dateFormat;
        $this->messageFormat = $messageFormat;
    }

    public function format(string $level, string $message, array $context = []): string
    {
        $datetime      = date($this->dateFormat);
        $contextString = empty($context) ? '' : json_encode($context, JSON_UNESCAPED_UNICODE);

        return str_replace(
                ['{datetime}', '{level}', '{message}', '{context}'],
                [$datetime, strtoupper($level), $message, $contextString],
                $this->messageFormat
            ) . PHP_EOL;
    }

}