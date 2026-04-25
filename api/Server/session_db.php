<?php
class DbSessionHandler implements SessionHandlerInterface {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function open($path, $name): bool { return true; }
    public function close(): bool { return true; }

    public function read($id): string {
        $id  = $this->db->real_escape_string($id);
        $res = $this->db->query("SELECT data FROM php_sessions WHERE id='$id' AND expires > NOW() LIMIT 1");
        if ($res && $res->num_rows > 0) return $res->fetch_assoc()['data'];
        return '';
    }

    public function write($id, $data): bool {
        $id      = $this->db->real_escape_string($id);
        $data    = $this->db->real_escape_string($data);
        $expires = date('Y-m-d H:i:s', time() + 7200);
        $this->db->query("INSERT INTO php_sessions (id, data, expires)
                          VALUES ('$id','$data','$expires')
                          ON DUPLICATE KEY UPDATE data='$data', expires='$expires'");
        return true;
    }

    public function destroy($id): bool {
        $id = $this->db->real_escape_string($id);
        $this->db->query("DELETE FROM php_sessions WHERE id='$id'");
        return true;
    }

    public function gc($max_lifetime): int|false {
        $this->db->query("DELETE FROM php_sessions WHERE expires < NOW()");
        return $this->db->affected_rows;
    }
}

function startDbSession(mysqli $conn): void {
    $handler = new DbSessionHandler($conn);
    session_set_save_handler($handler, true);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', '1');
    session_start();
}
