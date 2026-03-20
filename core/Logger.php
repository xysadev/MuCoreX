<?php
class Logger {
    private string $logDir;

    public function __construct(string $logDir = null) {
        $this->logDir = $logDir ?? __DIR__ . '/../logs';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function log(string $message): void {
        $filename = $this->logDir . '/app-' . date('Y-m-d') . '.log';
        $entry = date('[Y-m-d H:i:s] ') . $message . PHP_EOL;

        try {
            file_put_contents($filename, $entry, FILE_APPEND | LOCK_EX);
        } catch (Throwable $e) {
            error_log("Logger Error: " . $e->getMessage());
        }
    }
}