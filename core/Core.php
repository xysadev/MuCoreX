<?php
class Core {
    protected Database $db;
    protected Logger $logger;
    private bool $forceHttps;

    public function __construct(Database $db, Logger $logger, bool $forceHttps = false) {
        $this->db = $db;
        $this->logger = $logger;
        $this->forceHttps = $forceHttps;
    }

    public function json(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function getBearerToken(): string {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!$auth && function_exists('getallheaders')) {
            $headers = getallheaders();
            $auth = $headers['Authorization'] ?? '';
        }

        if (preg_match('/Bearer\s(\S+)/', $auth, $m)) {
            return $m[1];
        }

        return '';
    }

    public function validateToken(): array {
        $token = $this->getBearerToken();

        if (!$token) {
            $this->json(['status'=>'error','message'=>'Missing token']);
        }

        $row = $this->db->fetchSingle(
            "SELECT user_id 
             FROM users_tokens 
             WHERE api_token = :token 
             AND expires_at > GETDATE()",
            ['token'=>$token]
        );

        if (!$row) {
            $this->json(['status'=>'error','message'=>'Unauthorized']);
        }

        return $row;
    }

    public function requireAuth(): int {
        return $this->validateToken()['user_id'];
    }

    public function query(string $sql, array $params = []): array {
        try {
            return $this->db->secureQuery($sql, $params);
        } catch (PDOException $e) {
            $this->logger->log("DB Error: " . $e->getMessage());
            $this->json(['status'=>'error','message'=>'Database query failed']);
        }
    }

    public function querySingle(string $sql, array $params = []): ?array {
        try {
            return $this->db->fetch($sql, $params);
        } catch (PDOException $e) {
            $this->logger->log("DB Error: " . $e->getMessage());
            $this->json(['status'=>'error','message'=>'Database query failed']);
        }
    }

    public function sensitiveQuery(string $sql, array $params = [], string $context = 'unknown'): array {
        try {
            $result = $this->db->secureQuery($sql, $params);
            $this->logger->log("Sensitive query executed in context '{$context}'");
            return $result;
        } catch (PDOException $e) {
            $this->logger->log("Sensitive query FAILED in '{$context}': " . $e->getMessage());
            $this->json(['status'=>'error','message'=>'Database query failed']);
        }
    }

    public function sensitiveQuerySingle(string $sql, array $params = [], string $context = 'unknown'): ?array {
        try {
            $result = $this->db->fetch($sql, $params);
            $this->logger->log("Sensitive querySingle executed in context '{$context}'");
            return $result;
        } catch (PDOException $e) {
            $this->logger->log("Sensitive querySingle FAILED in '{$context}': " . $e->getMessage());
            $this->json(['status'=>'error','message'=>'Database query failed']);
        }
    }
}