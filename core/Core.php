<?php

class Core {
    protected Database $db;

    private ?array $auth = null;
    private bool $authLoaded = false;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /* =========================
       RESPONSE
    ========================= */

    public function json(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function fail(string $message, int $code = 400): never {
        $this->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }

    /* =========================
       AUTH
    ========================= */

    public function auth(bool $required = false): ?array {
        if ($this->authLoaded) {
            return $this->auth;
        }

        $token = $this->getBearerToken();

        if (!$token) {
            $this->authLoaded = true;

            if ($required) {
                $this->fail('Missing token', 401);
            }

            return null;
        }

        $row = $this->db->fetch("
            SELECT user_id, last_action_at
            FROM users_tokens
            WHERE api_token = :token
              AND expires_at > GETDATE()
        ", [
            'token' => $token
        ]);

        if (!$row) {
            $this->authLoaded = true;

            if ($required) {
                $this->fail('Unauthorized', 401);
            }

            return null;
        }

        /* audit optimizado (evita spam writes) */
        $this->db->execute("
            UPDATE users_tokens
            SET last_action_at = GETDATE()
            WHERE api_token = :token
              AND DATEDIFF(SECOND, last_action_at, GETDATE()) > 30
        ", [
            'token' => $token
        ]);

        $this->auth = $row;
        $this->authLoaded = true;

        return $this->auth;
    }

    /* =========================
       USER HELPERS
    ========================= */

    public function userId(): ?string {
        $auth = $this->auth();
        return $auth['user_id'] ?? null;
    }

    public function requireUserId(): string {
        return $this->auth(true)['user_id'];
    }

    public function authData(): ?array {
        return $this->auth();
    }

    /* =========================
       TOKEN
    ========================= */

    public function getBearerToken(): string {
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        $auth = $headers['Authorization']
            ?? $headers['authorization']
            ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

        if (preg_match('/Bearer\s+(\S+)/', $auth, $m)) {
            return $m[1];
        }

        return '';
    }

    /* =========================
       DB SHORTCUTS
    ========================= */

    public function query(string $sql, array $params = []): array {
        return $this->db->fetchAll($sql, $params);
    }

    public function queryOne(string $sql, array $params = []): ?array {
        return $this->db->fetch($sql, $params);
    }

    public function execute(string $sql, array $params = []): int {
        return $this->db->execute($sql, $params);
    }
}