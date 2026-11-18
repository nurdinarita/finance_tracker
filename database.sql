-- ============================================================
-- MoneyTracker Pro (Money+ v6.6.0 Feature-Complete) - MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS moneytracker_pro
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE moneytracker_pro;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS bill_tag;
DROP TABLE IF EXISTS tag;
DROP TABLE IF EXISTS goal_record;
DROP TABLE IF EXISTS goal;
DROP TABLE IF EXISTS budget;
DROP TABLE IF EXISTS account_change_log;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS member;
DROP TABLE IF EXISTS account;
DROP TABLE IF EXISTS account_book;
DROP TABLE IF EXISTS app_settings;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE app_settings (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  active_book_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
  theme VARCHAR(32) NOT NULL DEFAULT 'theme_blue',
  lang VARCHAR(8) NOT NULL DEFAULT 'id',
  currency VARCHAR(8) NOT NULL DEFAULT 'IDR',
  widget_style VARCHAR(16) NOT NULL DEFAULT 'compact',
  security_enabled TINYINT(1) NOT NULL DEFAULT 0,
  pin_code VARCHAR(16) NOT NULL DEFAULT '1234',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE account_book (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  note VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- account_type: 1=Tunai, 2=Kartu Deposit, 3=Kartu Kredit, 4=Akun Virtual, 5=Berinvestasi, 6=Piutang, 7=Hutang
CREATE TABLE account (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_type TINYINT NOT NULL DEFAULT 1,
  name VARCHAR(120) NOT NULL,
  currency_code VARCHAR(16) NOT NULL DEFAULT 'IDR',
  balance DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
  note VARCHAR(255) DEFAULT '',
  disuse TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE account_change_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_id BIGINT UNSIGNED NOT NULL,
  tx_id VARCHAR(64) NULL,
  content VARCHAR(255) DEFAULT '',
  change_amount DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_acl_acc FOREIGN KEY (account_id) REFERENCES account(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE member (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transactions (
  id VARCHAR(64) NOT NULL PRIMARY KEY,
  book_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
  type ENUM('expense', 'income', 'transfer') NOT NULL DEFAULT 'expense',
  category_id VARCHAR(32) NOT NULL,
  account_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
  transfer_to_id BIGINT UNSIGNED NULL,
  member_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
  amount DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
  note TEXT NULL,
  tx_date DATE NOT NULL,
  tx_time VARCHAR(5) NOT NULL DEFAULT '12:00',
  location_name VARCHAR(150) NULL,
  location_coords VARCHAR(64) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tx_book_date (book_id, tx_date),
  INDEX idx_tx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tag (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
  name VARCHAR(64) NOT NULL,
  color VARCHAR(16) NOT NULL DEFAULT '#2563eb',
  is_pinned TINYINT(1) NOT NULL DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bill_tag (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tx_id VARCHAR(64) NOT NULL,
  tag_id BIGINT UNSIGNED NOT NULL,
  CONSTRAINT fk_bt_tx FOREIGN KEY (tx_id) REFERENCES transactions(id) ON DELETE CASCADE,
  CONSTRAINT fk_bt_tag FOREIGN KEY (tag_id) REFERENCES tag(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE budget (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
  name VARCHAR(120) NOT NULL,
  category_id VARCHAR(32) NULL,
  period_type ENUM('weekly', 'monthly', 'yearly') NOT NULL DEFAULT 'monthly',
  amount DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
  note VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE goal (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
  name VARCHAR(120) NOT NULL,
  target_amount DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
  goal_type ENUM('flexible', 'cyclic') NOT NULL DEFAULT 'flexible',
  note VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE goal_record (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  goal_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
  note VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gr_goal FOREIGN KEY (goal_id) REFERENCES goal(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Data Default
INSERT INTO app_settings (id, active_book_id, theme, lang, currency, widget_style, security_enabled, pin_code)
VALUES (1, 1, 'theme_blue', 'id', 'IDR', 'compact', 0, '1234');

INSERT INTO account_book (id, name, note) VALUES
(1, 'Buku Harian', 'Untuk Penggunaan Sehari-hari'),
(2, 'Perjalanan', 'Catatan Liburan & Traveling');

INSERT INTO account (id, account_type, name, currency_code, balance, note) VALUES
(1, 1, 'Tunai', 'IDR', 1250000.00, 'Uang tunai di dompet'),
(2, 2, 'Kartu Deposit BCA', 'IDR', 8500000.00, 'Rekening utama'),
(3, 4, 'Dompet Digital', 'IDR', 640000.00, 'GoPay / OVO / QRIS'),
(4, 3, 'Kartu Kredit', 'IDR', -450000.00, 'Tagihan berjalan'),
(5, 5, 'Investasi Reksadana', 'IDR', 5000000.00, 'Tabungan jangka panjang');

INSERT INTO member (id, name) VALUES
(1, 'Diri Sendiri'),
(2, 'Keluarga');

INSERT INTO tag (id, book_id, name, color, is_pinned) VALUES
(1, 1, 'MakanSiang', '#f97316', 1),
(2, 1, 'Wajib', '#2563eb', 1),
(3, 1, 'Promo', '#10b981', 0);

INSERT INTO transactions (id, book_id, type, category_id, account_id, member_id, amount, note, tx_date, tx_time, location_name, location_coords) VALUES
('tx_101', 1, 'expense', 'food', 3, 1, 45000.00, 'Makan siang Nasi Padang & Es Teh', CURDATE(), '12:30', 'BSD City, Tangerang', '-6.3019, 106.6527'),
('tx_102', 1, 'expense', 'transport', 1, 1, 28000.00, 'KRL Commuter & Ojek Online', CURDATE(), '08:15', 'Stasiun Rawa Buntu', '-6.3145, 106.6762'),
('tx_103', 1, 'income', 'freelance', 2, 1, 750000.00, 'Pembayaran Desain UI Mobile App', CURDATE(), '10:00', NULL, NULL),
('tx_104', 1, 'expense', 'shopping', 2, 2, 215000.00, 'Belanja kebutuhan bulanan supermarket', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '19:20', 'AEON Mall BSD', '-6.3046, 106.6442'),
('tx_105', 1, 'expense', 'bills', 2, 1, 350000.00, 'Tagihan Internet Fiber & Token Listrik', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '14:10', NULL, NULL),
('tx_106', 1, 'income', 'salary', 2, 1, 8500000.00, 'Gaji Bulanan Utama', DATE_SUB(CURDATE(), INTERVAL 4 DAY), '09:00', 'Kantor Pusat Jakarta', '-6.2088, 106.8456');

INSERT INTO bill_tag (tx_id, tag_id) VALUES ('tx_101', 1), ('tx_104', 2);

INSERT INTO budget (id, book_id, name, category_id, period_type, amount, note) VALUES
(1, 1, 'Anggaran Bulanan Global', NULL, 'monthly', 4000000.00, 'Batas total pengeluaran bulanan'),
(2, 1, 'Anggaran Makan & Minum', 'food', 'monthly', 1500000.00, 'Fokus hemat makan harian');

INSERT INTO goal (id, book_id, name, target_amount, goal_type, note) VALUES
(1, 1, 'Dana Darurat', 20000000.00, 'flexible', 'Target 6 bulan biaya hidup'),
(2, 1, 'Liburan Akhir Tahun', 6000000.00, 'cyclic', 'Tabungan tiket & hotel');

INSERT INTO goal_record (goal_id, amount, note) VALUES
(1, 5000000.00, 'Setoran awal'),
(2, 1500000.00, 'Tabungan bulan pertama');

DROP TABLE IF EXISTS investments;

CREATE TABLE investments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
  asset_type ENUM('stock', 'gold', 'crypto', 'mutual_fund', 'other') NOT NULL DEFAULT 'stock',
  name VARCHAR(120) NOT NULL,
  ticker VARCHAR(32) DEFAULT '',
  unit_label VARCHAR(16) NOT NULL DEFAULT 'lembar',
  quantity DECIMAL(18, 6) NOT NULL DEFAULT 0,
  buy_price_per_unit DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
  invested_amount DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
  current_price_per_unit DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
  last_price_update TIMESTAMP NULL,
  note VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO investments (book_id, asset_type, name, ticker, unit_label, quantity, buy_price_per_unit, invested_amount, current_price_per_unit, note) VALUES
(1, 'stock', 'Bank Central Asia', 'BBCA.JK', 'lembar', 500, 9500.00, 4750000.00, 10150.00, '5 Lot Saham BBCA'),
(1, 'gold', 'Emas Antam LM', 'XAU', 'gram', 5.000000, 1380000.00, 6900000.00, 1495000.00, 'Tabungan Emas Fisik'),
(1, 'mutual_fund', 'Reksadana Pasar Uang', 'RDPU', 'unit', 2500.000000, 1000.00, 2500000.00, 1045.00, 'Dana parkir likuid'),
(1, 'crypto', 'Bitcoin', 'BTC', 'koin', 0.005000, 950000000.00, 4750000.00, 1420000000.00, 'DCA Bulanan');
