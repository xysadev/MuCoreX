<?php
class Auth {
    private Database $db;
    private Logger $logger;

    public function __construct(Database $db, Logger $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    // Validar token y devolver user_id
    public function validateToken(): int {
        // Intenta obtener Bearer token estándar
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!$auth && function_exists('getallheaders')) {
            $headers = getallheaders();
            $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        // Fallback para header custom (localhost/testing)
        if (!$auth) {
            $auth = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? '';
        }

        preg_match('/Bearer\s(\S+)/', $auth, $matches);
        $token = $matches[1] ?? $auth; // si no es Bearer, toma lo que haya

        if (!$token) {
            $this->json(['status'=>'error','message'=>'Unauthorized: no token']);
        }

        $row = $this->db->fetchSingle("
            SELECT user_id, expires_at
            FROM users_tokens
            WHERE api_token = :token
        ", ['token' => $token]);

        if (!$row) {
            $this->json(['status'=>'error','message'=>'Unauthorized: invalid token']);
        }

        if (strtotime($row['expires_at']) < time()) {
            $this->json(['status'=>'error','message'=>'Unauthorized: token expired']);
        }

        return (int)$row['user_id'];
    }

    // JSON helper para respuestas
    public function json(array $data): void {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode($data);
        exit;
    }
}