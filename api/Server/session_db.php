<?php
/**
 * Server/session_db.php
 * Custom session handler — simpan session di TiDB Cloud
 * Kompatibel dengan Vercel (serverless, HTTPS)
 */

class DbSessionHandler implements SessionHandlerInterface
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $id  = $this->db->real_escape_string($id);
        $res = $this->db->query(
            "SELECT data FROM php_sessions WHERE id='$id' AND expires > NOW() LIMIT 1"
        );
        if ($res && $res->num_rows > 0) {
            return $res->fetch_assoc()['data'];
        }
        return '';
    }

    public function write(string $id, string $data): bool
    {
        $id      = $this->db->real_escape_string($id);
        $data    = $this->db->real_escape_string($data);
        $expires = date('Y-m-d H:i:s', time() + 7200); // 2 jam
        $this->db->query(
            "INSERT INTO php_sessions (id, data, expires)
             VALUES ('$id', '$data', '$expires')
             ON DUPLICATE KEY UPDATE data='$data', expires='$expires'"
        );
        return true;
    }

    public function destroy(string $id): bool
    {
        $id = $this->db->real_escape_string($id);
        $this->db->query("DELETE FROM php_sessions WHERE id='$id'");
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $this->db->query("DELETE FROM php_sessions WHERE expires < NOW()");
        return $this->db->affected_rows;
    }
}

function startDbSession(mysqli $conn): void
{
    $handler = new DbSessionHandler($conn);
    session_set_save_handler($handler, true);

    // Cookie setting yang kompatibel dengan Vercel HTTPS
    session_set_cookie_params([
        'lifetime' => 7200,
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,  // wajib karena Vercel pakai HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Pastikan belum ada session aktif sebelum start
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}