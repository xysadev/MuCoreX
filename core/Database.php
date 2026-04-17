<?php

class Database {
    private PDO $pdo;
    private array $config;
    private int $maxRetries = 2;

    public function __construct(array $config) {
        if (empty($config['db']) || !is_array($config['db'])) {
            $this->fail('Configuración DB inválida');
        }

        $this->config = $config['db'];
        $this->connect();
    }

    private function connect(): void {
        $isProd = ($this->config['env'] ?? 'dev') === 'prod';

        $encrypt = $isProd ? 'Yes' : 'No';
        $trust = $isProd ? ';TrustServerCertificate=Yes' : '';

        $dsn = sprintf(
            "sqlsrv:Server=%s;Database=%s;Encrypt=%s%s",
            $this->config['host'],
            $this->config['dbname'],
            $encrypt,
            $trust
        );

        $attempts = 0;

        while ($attempts <= $this->maxRetries) {
            try {
                $this->pdo = new PDO(
                    $dsn,
                    $this->config['user'],
                    $this->config['pass'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                return;

            } catch (PDOException $e) {
                $attempts++;
                error_log("[DB CONNECT ERROR] " . $e->getMessage());

                if ($attempts > $this->maxRetries) {
                    $this->fail('Conexión fallida a la base de datos');
                }

                usleep(500000);
            }
        }
    }

    /* =========================
       CORE OPS
    ========================= */

    public function execute(string $sql, array $params = []): int {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("[DB EXEC ERROR] " . $e->getMessage());
            throw new Exception($this->isProd() ? 'Database error' : $e->getMessage());
        }
    }

    public function fetch(string $sql, array $params = []): ?array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /* =========================
       TRANSACTIONS
    ========================= */

    public function begin(): void {
        $this->pdo->beginTransaction();
    }

    public function commit(): void {
        $this->pdo->commit();
    }

    public function rollback(): void {
        $this->pdo->rollBack();
    }

    /* =========================
       CONFIG HELPERS
    ========================= */

    public function isProd(): bool {
        return ($this->config['env'] ?? 'dev') === 'prod';
    }

    public function forceHttps(): bool {
        return (bool) ($this->config['force_https'] ?? false);
    }

    public function getApiToken(): string {
        return $this->config['api_token'] ?? '';
    }

    /* =========================
       ERROR HANDLER
    ========================= */

    private function fail(string $message): void {
        error_log("[DB ERROR] " . $message);
        throw new Exception(
            $this->isProd() ? 'Database error' : $message
        );
    }
}