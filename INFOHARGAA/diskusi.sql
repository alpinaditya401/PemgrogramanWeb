-- ================================================================
--  InfoHarga Komoditi — diskusi.sql
--  Tabel untuk fitur Diskusi/Chat antar pengguna
--
--  Cara pakai:
--  phpMyAdmin → pilih infoharga_db → tab SQL → paste → Go
-- ================================================================

USE infoharga_db;

-- ── Tabel diskusi (komentar utama per komoditas) ──────────────
CREATE TABLE IF NOT EXISTS diskusi (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  komoditas_id INT UNSIGNED     DEFAULT NULL
    COMMENT 'Relasi ke tabel komoditas (NULL = diskusi umum)',
  user_id      INT UNSIGNED     NOT NULL,
  parent_id    INT UNSIGNED     DEFAULT NULL
    COMMENT 'NULL = komentar utama, isi = balasan (reply)',
  pesan        TEXT             NOT NULL,
  is_deleted   TINYINT(1)       NOT NULL DEFAULT 0
    COMMENT '1 = sudah dihapus (soft delete)',
  created_at   TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_komoditas (komoditas_id),
  INDEX idx_user      (user_id),
  INDEX idx_parent    (parent_id),
  INDEX idx_created   (created_at),
  FOREIGN KEY (user_id)      REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (parent_id)    REFERENCES diskusi(id)  ON DELETE CASCADE,
  FOREIGN KEY (komoditas_id) REFERENCES komoditas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Komentar dan diskusi antar pengguna tentang harga komoditas';

-- ── Tabel reaksi komentar (like/helpful) ─────────────────────
CREATE TABLE IF NOT EXISTS diskusi_reaksi (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  diskusi_id INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  tipe       ENUM('like','helpful') NOT NULL DEFAULT 'like',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reaksi (diskusi_id, user_id, tipe),
  FOREIGN KEY (diskusi_id) REFERENCES diskusi(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Reaksi like/helpful pada komentar diskusi';
