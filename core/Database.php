<?php

class Database {
    private PDO $pdo;
    private array $config;
    private int $maxRetries = 2;

    public function __construct(array $config) {
        if (empty($config['db']) || !is_array($config['db'])) {
            $this->handleError('Configuración DB inválida');
        }

        $this->config = $config['db'];
        $this->connect();
    }

    private function connect(): void {
        $encrypt = ($this->config['env'] ?? 'dev') === 'prod' ? 'Yes' : 'No';
        $trust   = $encrypt === 'Yes' ? ';TrustServerCertificate=Yes' : '';
        $dsn = "sqlsrv:Server={$this->config['host']};Database={$this->config['dbname']};Encrypt={$encrypt}{$trust}";

        $attempts = 0;
        while ($attempts <= $this->maxRetries) {
            try {
                $this->pdo = new PDO(
                    $dsn,
                    $this->config['user'],
                    $this->config['pass'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                return;
            } catch (PDOException $e) {
                $attempts++;
                if ($attempts > $this->maxRetries) {
                    $this->handleError('Conexión fallida a la base de datos');
                }
                sleep(1);
            }
        }
    }

    public function execute(string $sql, array $params = []): int {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fetch(string $sql, array $params = []): ?array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function fetchSingle(string $sql, array $params = []): ?array {
        return $this->fetch($sql, $params);
    }

    public function secureQuery(string $sql, array $params = []): array {
        return $this->fetchAll($sql, $params);
    }

    public function getApiToken(): string {
        return $this->config['api_token'] ?? '';
    }

    public function forceHttps(): bool {
        return $this->config['force_https'] ?? false;
    }

    public function isProd(): bool {
        return ($this->config['env'] ?? 'dev') === 'prod';
    }

    private function handleError(string $message): void {
        error_log("[Database Error] " . $message);
        if ($this->isProd()) {
            die(json_encode(['status'=>'error','message'=>'Error de conexión']));
        } else {
            die(json_encode(['status'=>'error','message'=>$message]));
        }
    }
}
?>