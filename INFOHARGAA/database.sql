-- ================================================================
--  InfoHarga Komoditi — database.sql v3.0
--  Cara pakai: phpMyAdmin > tab SQL > paste > klik Go
-- ================================================================

CREATE DATABASE IF NOT EXISTS infoharga_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE infoharga_db;

-- ── TABEL USERS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email        VARCHAR(120) NOT NULL UNIQUE,
  username     VARCHAR(60)  NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(120) DEFAULT NULL,
  tgl_lahir    DATE         DEFAULT NULL,
  role         ENUM('admin','kontributor') NOT NULL DEFAULT 'kontributor',
  is_active    TINYINT(1)   NOT NULL DEFAULT 1,
  last_login   DATETIME     DEFAULT NULL,
  created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── TABEL KOMODITAS ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS komoditas (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama            VARCHAR(120) NOT NULL,
  kategori        ENUM('Beras & Serealia','Hortikultura','Bumbu & Rempah',
                       'Peternakan','Minyak & Lemak','Perikanan','Lainnya')
                  NOT NULL DEFAULT 'Lainnya',
  lokasi          VARCHAR(120) NOT NULL,
  provinsi        VARCHAR(100) NOT NULL DEFAULT '',
  satuan          VARCHAR(30)  NOT NULL DEFAULT 'kg',
  harga_kemarin   BIGINT UNSIGNED NOT NULL DEFAULT 0,
  harga_sekarang  BIGINT UNSIGNED NOT NULL DEFAULT 0,
  history         JSON         DEFAULT NULL,
  status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  submitted_by    INT UNSIGNED DEFAULT NULL,
  catatan_admin   VARCHAR(255) DEFAULT NULL,
  created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_nama (nama),
  INDEX idx_provinsi (provinsi)
) ENGINE=InnoDB;

-- ── TABEL ARTIKEL ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS artikel (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  judul       VARCHAR(255) NOT NULL,
  slug        VARCHAR(255) NOT NULL UNIQUE,
  ringkasan   TEXT         DEFAULT NULL,
  konten      LONGTEXT     DEFAULT NULL,
  kategori    VARCHAR(60)  NOT NULL DEFAULT 'Umum',
  emoji       VARCHAR(10)  DEFAULT '📰',
  menit_baca  TINYINT UNSIGNED DEFAULT 5,
  penulis_id  INT UNSIGNED DEFAULT NULL,
  is_publish  TINYINT(1)   NOT NULL DEFAULT 1,
  views       INT UNSIGNED NOT NULL DEFAULT 0,
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (penulis_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_publish (is_publish),
  FULLTEXT idx_search (judul, ringkasan)
) ENGINE=InnoDB;

-- ── DATA AWAL: ADMIN ────────────────────────────────────────
-- password = "password"
INSERT INTO users (email, username, password, nama_lengkap, role) VALUES
('admin@infoharga.com', 'admin',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Administrator', 'admin');

-- password = "password"
INSERT INTO users (email, username, password, nama_lengkap, role) VALUES
('kontributor@infoharga.com', 'kontributor1',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Kontributor Pertama', 'kontributor');

-- ── DATA AWAL: KOMODITAS ────────────────────────────────────
INSERT INTO komoditas (nama, kategori, lokasi, provinsi, satuan, harga_kemarin, harga_sekarang, history, status) VALUES
('Beras Premium',  'Beras & Serealia', 'Jakarta',    'DKI Jakarta',    'kg', 15000, 15500, '[14200,14500,14300,14800,15200,15000,15500]', 'approved'),
('Beras Medium',   'Beras & Serealia', 'Bandung',    'Jawa Barat',     'kg', 14500, 14500, '[14000,14100,14200,14300,14400,14500,14500]', 'approved'),
('Cabai Merah',    'Hortikultura',     'Surabaya',   'Jawa Timur',     'kg', 65000, 62000, '[68000,67000,69000,68000,66000,65000,62000]', 'approved'),
('Bawang Merah',   'Bumbu & Rempah',   'Brebes',     'Jawa Tengah',    'kg', 23000, 24500, '[20000,21000,22000,22500,23000,23000,24500]', 'approved'),
('Bawang Putih',   'Bumbu & Rempah',   'Medan',      'Sumatera Utara', 'kg', 35000, 33000, '[38000,37000,36500,36000,35500,35000,33000]', 'approved'),
('Minyak Goreng',  'Minyak & Lemak',   'Makassar',   'Sulawesi Selatan','liter',18000,17500,'[19000,18500,18000,18200,18000,18000,17500]','approved'),
('Gula Pasir',     'Lainnya',          'Yogyakarta', 'DI Yogyakarta',  'kg', 16000, 16000, '[15500,15500,15800,16000,16000,16000,16000]', 'approved'),
('Daging Sapi',    'Peternakan',       'Surabaya',   'Jawa Timur',     'kg',130000,135000, '[125000,127000,128000,130000,130000,130000,135000]','approved'),
('Telur Ayam',     'Peternakan',       'Jakarta',    'DKI Jakarta',    'butir',  2000,  2200, '[1800,1900,1950,2000,2000,2000,2200]', 'approved'),
('Ikan Bandeng',   'Perikanan',        'Semarang',   'Jawa Tengah',    'kg', 32000, 30000, '[35000,34000,33000,33000,32000,32000,30000]', 'approved');

-- ── DATA AWAL: ARTIKEL ──────────────────────────────────────
INSERT INTO artikel (judul, slug, ringkasan, kategori, emoji, menit_baca, is_publish) VALUES
('Mengenal Jenis Beras dan Pengaruhnya terhadap Harga Pasar',
 'mengenal-jenis-beras-harga-pasar',
 'Beras premium, medium, dan IR64 memiliki karakteristik berbeda yang memengaruhi harga jual di pasar. Ketahui faktor-faktor yang memengaruhi fluktuasi harga beras nasional.',
 'Beras & Serealia', '🌾', 5, 1),

('Mengapa Harga Cabai Sangat Fluktuatif? Ini Penjelasannya',
 'mengapa-harga-cabai-fluktuatif',
 'Cabai merah dan rawit dikenal sebagai komoditas dengan volatilitas harga tertinggi di Indonesia. Pelajari faktor musim, distribusi, dan spekulasi yang memengaruhinya.',
 'Hortikultura', '🌶️', 4, 1),

('Bawang Merah & Putih: Komoditas Strategis yang Perlu Dipantau',
 'bawang-merah-putih-komoditas-strategis',
 'Sebagai bahan dasar masakan Indonesia, bawang merupakan indikator penting ketahanan pangan. Simak peta produksi dan distribusinya di seluruh nusantara.',
 'Bumbu & Rempah', '🧅', 6, 1),

('Dinamika Harga Daging Sapi dan Ayam di Pasar Tradisional',
 'dinamika-harga-daging-sapi-ayam',
 'Harga daging sapi dan ayam broiler dipengaruhi oleh biaya pakan, rantai distribusi, dan kebijakan impor. Pahami cara membaca tren harga untuk kebutuhan sehari-hari.',
 'Peternakan', '🥩', 5, 1),

('Harga Minyak Goreng dan Hubungannya dengan Harga CPO Global',
 'harga-minyak-goreng-cpo-global',
 'Indonesia sebagai produsen CPO terbesar dunia tetap rentan terhadap guncangan harga minyak goreng domestik. Temukan hubungan antara harga global dan harga di warung.',
 'Minyak & Lemak', '🛢️', 7, 1),

('Mengenal Komoditas Ikan Tangkap dan Budidaya yang Sering Dipantau',
 'komoditas-ikan-tangkap-budidaya',
 'Ikan bandeng, lele, dan udang windu adalah komoditas perikanan yang harganya dipantau pemerintah. Pelajari faktor musim tangkap dan cuaca yang memengaruhi pasokan.',
 'Perikanan', '🐟', 5, 1),

('Memahami Rantai Distribusi Pangan dari Petani ke Konsumen',
 'rantai-distribusi-pangan-petani-konsumen',
 'Rantai distribusi yang panjang sering menjadi penyebab mahalnya harga pangan di tingkat konsumen. Pelajari bagaimana data harga komoditas dapat membantu memotong rantai yang tidak efisien.',
 'Umum', '🚜', 6, 1),

('Peran Teknologi Digital dalam Transparansi Harga Komoditas',
 'teknologi-digital-transparansi-harga-komoditas',
 'Platform digital seperti InfoHarga memungkinkan petani, pedagang, dan konsumen mengakses informasi harga secara real-time. Ini adalah langkah penting menuju pasar yang lebih adil.',
 'Umum', '💻', 4, 1);

-- ================================================================
--  Login default:
--  Admin       → username: admin        | password: password
--  Kontributor → username: kontributor1 | password: password
-- ================================================================
