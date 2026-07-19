-- =============================================
-- JAXPAY Digital Wallet System - Database
-- Data Dummy Lengkap untuk Demo
-- 20 Users: 12 Student, 3 Guru, 2 Ortu, 3 Merchant
-- =============================================

CREATE DATABASE IF NOT EXISTS jaxpay_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jaxpay_db;

-- =============================================
-- TABLES
-- =============================================

CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_hp VARCHAR(20),
    nis_nim VARCHAR(30),
    kelas VARCHAR(50),
    role ENUM('student','teacher','parent','merchant') DEFAULT 'student',
    saldo DECIMAL(15,2) DEFAULT 0.00,
    foto VARCHAR(255) DEFAULT 'default.png',
    member_level ENUM('Basic','Silver','Gold','Platinum') DEFAULT 'Basic',
    member_id VARCHAR(20) UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    kode VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(30) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    tipe ENUM('topup','transfer_masuk','transfer_keluar','pembayaran','qr_payment') NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    saldo_sebelum DECIMAL(15,2) NOT NULL,
    saldo_sesudah DECIMAL(15,2) NOT NULL,
    keterangan VARCHAR(255),
    ref_id INT DEFAULT NULL,
    status ENUM('pending','success','failed','cancelled') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE topup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_topup VARCHAR(30) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    metode_bayar VARCHAR(50) DEFAULT 'Transfer Bank',
    bukti_bayar VARCHAR(255),
    catatan TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_id INT DEFAULT NULL,
    admin_note TEXT DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE merchant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_toko VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(50),
    qr_code VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    total_transaksi INT DEFAULT 0,
    total_omzet DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE qr_payment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_qr VARCHAR(50) UNIQUE NOT NULL,
    merchant_id INT NOT NULL,
    buyer_id INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    keterangan VARCHAR(255),
    status ENUM('pending','success','failed','expired') DEFAULT 'pending',
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (merchant_id) REFERENCES merchant(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(100) NOT NULL,
    pesan TEXT NOT NULL,
    tipe ENUM('topup','transfer','pembayaran','info','promo') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin','user') NOT NULL,
    user_id INT NOT NULL,
    aksi VARCHAR(255) NOT NULL,
    detail TEXT,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- =============================================
-- ADMIN  (password: password)
-- =============================================
INSERT INTO admin (username, password, nama, email) VALUES
('admin',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin',      'admin@jaxpay.id'),
('operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator Sekolah', 'operator@jaxpay.id');

-- =============================================
-- USERS  (20 orang)
-- id 1-12  = student
-- id 13-15 = teacher
-- id 16-17 = parent
-- id 18-20 = merchant
-- =============================================
INSERT INTO users (nama, email, no_hp, nis_nim, kelas, role, saldo, member_level, member_id, is_active, created_at) VALUES
('Ahmad Fauzi',           'ahmad@student.jaxpay.id',    '081234567801', '2022001', 'XII IPA 1',        'student',  185000, 'Silver',   'JAX-2022001', 1, '2024-08-01 07:00:00'),
('Siti Rahmawati',        'siti@student.jaxpay.id',     '081234567802', '2022002', 'XII IPA 1',        'student',   97500, 'Basic',    'JAX-2022002', 1, '2024-08-01 07:10:00'),
('Rizky Pratama',         'rizky@student.jaxpay.id',    '081234567803', '2022003', 'XII IPA 2',        'student',  230000, 'Silver',   'JAX-2022003', 1, '2024-08-01 07:20:00'),
('Ayu Lestari',           'ayu@student.jaxpay.id',      '081234567804', '2022004', 'XII IPA 2',        'student',   64000, 'Basic',    'JAX-2022004', 1, '2024-08-02 07:00:00'),
('Deni Firmansyah',       'deni@student.jaxpay.id',     '081234567805', '2022005', 'XII IPS 1',        'student',  310000, 'Gold',     'JAX-2022005', 1, '2024-08-02 07:15:00'),
('Putri Amelia',          'putri@student.jaxpay.id',    '081234567806', '2022006', 'XII IPS 1',        'student',  120000, 'Silver',   'JAX-2022006', 1, '2024-08-02 07:30:00'),
('Farhan Maulana',        'farhan@student.jaxpay.id',   '081234567807', '2022007', 'XI IPA 1',         'student',   55000, 'Basic',    'JAX-2022007', 1, '2024-08-03 07:00:00'),
('Nadia Safitri',         'nadia@student.jaxpay.id',    '081234567808', '2022008', 'XI IPA 1',         'student',  178000, 'Silver',   'JAX-2022008', 1, '2024-08-03 07:20:00'),
('Bagas Kurniawan',       'bagas@student.jaxpay.id',    '081234567809', '2022009', 'XI IPS 2',         'student',  220000, 'Gold',     'JAX-2022009', 1, '2024-08-03 07:40:00'),
('Indah Permatasari',     'indah@student.jaxpay.id',    '081234567810', '2022010', 'XI IPS 2',         'student',   33000, 'Basic',    'JAX-2022010', 1, '2024-08-04 07:00:00'),
('Gilang Ramadhan',       'gilang@student.jaxpay.id',   '081234567811', '2022011', 'X IPA 3',          'student',  150000, 'Silver',   'JAX-2022011', 1, '2024-08-04 07:20:00'),
('Mega Wulandari',        'mega@student.jaxpay.id',     '081234567812', '2022012', 'X IPS 1',          'student',   56000, 'Basic',    'JAX-2022012', 1, '2024-08-04 07:40:00'),
('Budi Santoso',          'budi@guru.jaxpay.id',        '081234567813', 'GTK-001', 'Guru Matematika',  'teacher',  490000, 'Gold',     'JAX-GTK001',  1, '2024-08-01 06:00:00'),
('Sari Dewi Utami',       'sari@guru.jaxpay.id',        '081234567814', 'GTK-002', 'Guru Bahasa Indo', 'teacher',  620000, 'Gold',     'JAX-GTK002',  1, '2024-08-01 06:10:00'),
('Hendra Kusuma',         'hendra@guru.jaxpay.id',      '081234567815', 'GTK-003', 'Guru Fisika',      'teacher',  800000, 'Platinum', 'JAX-GTK003',  1, '2024-08-01 06:20:00'),
('Dewi Susanti',          'dewi@parent.jaxpay.id',      '081234567816', 'OT-001',  '-',                'parent',   270000, 'Silver',   'JAX-OT001',   1, '2024-08-05 08:00:00'),
('Agus Setiawan',         'agus@parent.jaxpay.id',      '081234567817', 'OT-002',  '-',                'parent',   385000, 'Gold',     'JAX-OT002',   1, '2024-08-05 08:30:00'),
('Kantin Barokah',        'kantin@merchant.jaxpay.id',  '081234567818', 'MCH-001', '-',                'merchant', 3250000,'Platinum', 'JAX-MCH001',  1, '2024-08-01 05:00:00'),
('Toko Alat Tulis Bu Ani','toko@merchant.jaxpay.id',    '081234567819', 'MCH-002', '-',                'merchant', 1875000,'Gold',     'JAX-MCH002',  1, '2024-08-01 05:30:00'),
('Koperasi Sekolah',      'koperasi@merchant.jaxpay.id','081234567820', 'MCH-003', '-',                'merchant', 2100000,'Platinum', 'JAX-MCH003',  1, '2024-08-01 06:00:00');


-- =============================================
-- MERCHANT  (user_id 18,19,20)
-- =============================================
INSERT INTO merchant (user_id, nama_toko, deskripsi, kategori, is_active, total_transaksi, total_omzet) VALUES
(18, 'Kantin Barokah',         'Kantin utama sekolah, tersedia nasi, mie, jajanan dan minuman segar. Buka setiap hari sekolah.', 'Makanan & Minuman', 1, 312, 6850000),
(19, 'Toko Alat Tulis Bu Ani', 'Menyediakan lengkap alat tulis, buku, seragam, dan perlengkapan sekolah lainnya.',               'Peralatan',         1, 178, 3920000),
(20, 'Koperasi Sekolah',       'Koperasi resmi sekolah, menyediakan kebutuhan siswa dari alat belajar hingga makanan ringan.',   'Serba Ada',         1, 245, 4650000);

-- =============================================
-- TOP UP  (approved, pending, rejected)
-- =============================================
INSERT INTO topup (kode_topup, user_id, jumlah, metode_bayar, status, admin_id, admin_note, approved_at, created_at) VALUES
('TPU-2025001', 1,  200000, 'BCA Transfer',     'approved', 1, NULL, '2025-05-01 09:15:00', '2025-05-01 08:30:00'),
('TPU-2025002', 3,  150000, 'Mandiri Transfer',  'approved', 1, NULL, '2025-05-02 10:20:00', '2025-05-02 09:45:00'),
('TPU-2025003', 5,  500000, 'BNI Transfer',      'approved', 1, NULL, '2025-05-03 08:00:00', '2025-05-02 21:30:00'),
('TPU-2025004', 9,  300000, 'BCA Transfer',      'approved', 1, NULL, '2025-05-04 11:00:00', '2025-05-04 10:15:00'),
('TPU-2025005', 13, 500000, 'Mandiri Transfer',  'approved', 1, NULL, '2025-05-05 07:30:00', '2025-05-04 22:00:00'),
('TPU-2025006', 14, 300000, 'BRI Transfer',      'approved', 1, NULL, '2025-05-06 08:45:00', '2025-05-06 08:00:00'),
('TPU-2025007', 15, 500000, 'BNI Transfer',      'approved', 1, NULL, '2025-05-07 09:00:00', '2025-05-06 20:00:00'),
('TPU-2025008', 16, 200000, 'BCA Transfer',      'approved', 1, NULL, '2025-05-08 10:30:00', '2025-05-08 09:00:00'),
('TPU-2025009', 6,  100000, 'BSI Transfer',      'approved', 1, NULL, '2025-05-09 11:00:00', '2025-05-09 10:00:00'),
('TPU-2025010', 8,  150000, 'Mandiri Transfer',  'approved', 1, NULL, '2025-05-10 08:30:00', '2025-05-10 07:45:00'),
('TPU-2025011', 11, 250000, 'BCA Transfer',      'approved', 1, NULL, '2025-05-11 09:45:00', '2025-05-11 09:00:00'),
('TPU-2025012', 17, 300000, 'BNI Transfer',      'approved', 1, NULL, '2025-05-12 10:00:00', '2025-05-12 09:15:00'),
('TPU-2025013', 2,  100000, 'BCA Transfer',      'pending',  NULL, NULL, NULL, '2025-05-16 14:20:00'),
('TPU-2025014', 4,   50000, 'Mandiri Transfer',  'pending',  NULL, NULL, NULL, '2025-05-16 15:10:00'),
('TPU-2025015', 7,  200000, 'BRI Transfer',      'pending',  NULL, NULL, NULL, '2025-05-17 07:30:00'),
('TPU-2025016', 10,  75000, 'BSI Transfer',      'pending',  NULL, NULL, NULL, '2025-05-17 08:00:00'),
('TPU-2025017', 12, 150000, 'BNI Transfer',      'pending',  NULL, NULL, NULL, '2025-05-17 08:45:00'),
('TPU-2025018', 1,  500000, 'BCA Transfer',      'rejected', 1, 'Bukti transfer tidak jelas/buram', NULL, '2025-04-20 10:00:00'),
('TPU-2025019', 3,  100000, 'Mandiri Transfer',  'rejected', 1, 'Nominal tidak sesuai bukti',       NULL, '2025-04-25 14:00:00');


-- =============================================
-- TRANSAKSI  (88 baris, semua user aktif)
-- =============================================
INSERT INTO transaksi (kode_transaksi, user_id, tipe, jumlah, saldo_sebelum, saldo_sesudah, keterangan, status, created_at) VALUES
-- Ahmad Fauzi (id 1)
('TRX-2025-00001', 1,'topup',          200000,      0, 200000,'Top Up via BCA',                   'success','2025-05-01 09:15:00'),
('TRX-2025-00002', 1,'pembayaran',      25000, 200000, 175000,'Bayar di Kantin Barokah',           'success','2025-05-02 10:05:00'),
('TRX-2025-00003', 1,'pembayaran',      15000, 175000, 160000,'Bayar di Kantin Barokah',           'success','2025-05-05 12:10:00'),
('TRX-2025-00004', 1,'transfer_keluar', 50000, 160000, 110000,'Transfer ke Siti Rahmawati',        'success','2025-05-07 14:30:00'),
('TRX-2025-00005', 1,'qr_payment',      25000, 110000,  85000,'QR Pay di Toko Alat Tulis Bu Ani',  'success','2025-05-09 08:30:00'),
('TRX-2025-00006', 1,'pembayaran',      20000,  85000,  65000,'Bayar di Koperasi Sekolah',         'success','2025-05-12 13:00:00'),
('TRX-2025-00007', 1,'transfer_keluar', 30000,  65000,  35000,'Transfer ke Ayu Lestari',           'success','2025-05-14 15:00:00'),
('TRX-2025-00008', 1,'topup',          200000,  35000, 235000,'Top Up via BCA (kedua)',             'success','2025-05-15 09:00:00'),
('TRX-2025-00009', 1,'pembayaran',      50000, 235000, 185000,'Bayar di Kantin Barokah',           'success','2025-05-16 11:30:00'),
-- Siti Rahmawati (id 2)
('TRX-2025-00010', 2,'transfer_masuk',  50000,  47500,  97500,'Terima dari Ahmad Fauzi',           'success','2025-05-07 14:30:00'),
('TRX-2025-00011', 2,'pembayaran',      15000,  97500,  82500,'Bayar di Kantin Barokah',           'success','2025-05-08 10:10:00'),
('TRX-2025-00012', 2,'pembayaran',      10000,  82500,  72500,'Bayar di Koperasi Sekolah',         'success','2025-05-13 12:30:00'),
('TRX-2025-00013', 2,'transfer_keluar', 25000,  72500,  47500,'Transfer ke Farhan Maulana',        'success','2025-05-15 16:00:00'),
-- Rizky Pratama (id 3)
('TRX-2025-00014', 3,'topup',          150000,  80000, 230000,'Top Up via Mandiri',                'success','2025-05-02 10:20:00'),
('TRX-2025-00015', 3,'pembayaran',      20000, 230000, 210000,'Bayar di Kantin Barokah',           'success','2025-05-03 11:00:00'),
('TRX-2025-00016', 3,'qr_payment',      35000, 210000, 175000,'QR Pay di Koperasi Sekolah',        'success','2025-05-08 08:45:00'),
('TRX-2025-00017', 3,'transfer_keluar', 50000, 175000, 125000,'Transfer ke Nadia Safitri',         'success','2025-05-10 13:15:00'),
('TRX-2025-00018', 3,'pembayaran',      30000, 125000,  95000,'Bayar di Kantin Barokah',           'success','2025-05-14 12:00:00'),
('TRX-2025-00019', 3,'topup',          150000,  95000, 245000,'Top Up via Mandiri (kedua)',         'success','2025-05-16 09:30:00'),
-- Ayu Lestari (id 4)
('TRX-2025-00020', 4,'transfer_masuk',  30000,  34000,  64000,'Terima dari Ahmad Fauzi',           'success','2025-05-14 15:00:00'),
('TRX-2025-00021', 4,'pembayaran',      12000,  64000,  52000,'Bayar di Kantin Barokah',           'success','2025-05-15 10:30:00'),
('TRX-2025-00022', 4,'pembayaran',       8000,  52000,  44000,'Bayar di Koperasi Sekolah',         'success','2025-05-16 12:15:00'),
-- Deni Firmansyah (id 5)
('TRX-2025-00023', 5,'topup',          500000,      0, 500000,'Top Up via BNI',                    'success','2025-05-03 08:00:00'),
('TRX-2025-00024', 5,'pembayaran',      35000, 500000, 465000,'Bayar di Kantin Barokah',           'success','2025-05-04 11:30:00'),
('TRX-2025-00025', 5,'transfer_keluar',100000, 465000, 365000,'Transfer ke Bagas Kurniawan',       'success','2025-05-06 14:00:00'),
('TRX-2025-00026', 5,'qr_payment',      25000, 365000, 340000,'QR Pay di Toko Alat Tulis Bu Ani',  'success','2025-05-09 08:00:00'),
('TRX-2025-00027', 5,'pembayaran',      30000, 340000, 310000,'Bayar di Koperasi Sekolah',         'success','2025-05-13 12:45:00'),
-- Putri Amelia (id 6)
('TRX-2025-00028', 6,'topup',          100000,  20000, 120000,'Top Up via BSI',                    'success','2025-05-09 11:00:00'),
('TRX-2025-00029', 6,'pembayaran',      18000, 120000, 102000,'Bayar di Kantin Barokah',           'success','2025-05-10 10:00:00'),
('TRX-2025-00030', 6,'pembayaran',      15000, 102000,  87000,'Bayar di Koperasi Sekolah',         'success','2025-05-14 13:00:00'),
('TRX-2025-00031', 6,'transfer_keluar', 20000,  87000,  67000,'Transfer ke Mega Wulandari',        'success','2025-05-15 15:30:00'),
-- Farhan Maulana (id 7)
('TRX-2025-00032', 7,'transfer_masuk',  25000,  30000,  55000,'Terima dari Siti Rahmawati',        'success','2025-05-15 16:00:00'),
('TRX-2025-00033', 7,'pembayaran',      10000,  55000,  45000,'Bayar di Kantin Barokah',           'success','2025-05-16 10:20:00'),
-- Nadia Safitri (id 8)
('TRX-2025-00034', 8,'topup',          150000,  28000, 178000,'Top Up via Mandiri',                'success','2025-05-10 08:30:00'),
('TRX-2025-00035', 8,'transfer_masuk',  50000, 128000, 178000,'Terima dari Rizky Pratama',         'success','2025-05-10 13:15:00'),
('TRX-2025-00036', 8,'pembayaran',      22000, 178000, 156000,'Bayar di Kantin Barokah',           'success','2025-05-11 11:00:00'),
('TRX-2025-00037', 8,'qr_payment',      30000, 156000, 126000,'QR Pay di Koperasi Sekolah',        'success','2025-05-13 13:15:00'),
('TRX-2025-00038', 8,'pembayaran',      15000, 126000, 111000,'Bayar di Toko Alat Tulis Bu Ani',   'success','2025-05-15 09:30:00'),
-- Bagas Kurniawan (id 9)
('TRX-2025-00039', 9,'topup',          300000, 120000, 420000,'Top Up via BCA',                    'success','2025-05-04 11:00:00'),
('TRX-2025-00040', 9,'transfer_masuk', 100000, 320000, 420000,'Terima dari Deni Firmansyah',       'success','2025-05-06 14:00:00'),
('TRX-2025-00041', 9,'pembayaran',      30000, 420000, 390000,'Bayar di Kantin Barokah',           'success','2025-05-07 12:00:00'),
('TRX-2025-00042', 9,'pembayaran',      45000, 390000, 345000,'Bayar di Koperasi Sekolah',         'success','2025-05-12 11:00:00'),
('TRX-2025-00043', 9,'transfer_keluar', 75000, 345000, 270000,'Transfer ke Indah Permatasari',     'success','2025-05-14 14:00:00'),
('TRX-2025-00044', 9,'qr_payment',      50000, 270000, 220000,'QR Pay di Kantin Barokah',          'success','2025-05-16 12:30:00'),
-- Indah Permatasari (id 10)
('TRX-2025-00045',10,'transfer_masuk',  75000,      0,  75000,'Terima dari Bagas Kurniawan',       'success','2025-05-14 14:00:00'),
('TRX-2025-00046',10,'pembayaran',      12000,  75000,  63000,'Bayar di Kantin Barokah',           'success','2025-05-15 10:00:00'),
('TRX-2025-00047',10,'pembayaran',      10000,  63000,  53000,'Bayar di Koperasi Sekolah',         'success','2025-05-16 11:45:00'),
('TRX-2025-00048',10,'pembayaran',      20000,  53000,  33000,'Bayar di Toko Alat Tulis Bu Ani',   'success','2025-05-17 09:00:00'),
-- Gilang Ramadhan (id 11)
('TRX-2025-00049',11,'topup',          250000,      0, 250000,'Top Up via BCA',                    'success','2025-05-11 09:45:00'),
('TRX-2025-00050',11,'pembayaran',      20000, 250000, 230000,'Bayar di Kantin Barokah',           'success','2025-05-12 10:30:00'),
('TRX-2025-00051',11,'qr_payment',      35000, 230000, 195000,'QR Pay di Koperasi Sekolah',        'success','2025-05-14 12:00:00'),
('TRX-2025-00052',11,'transfer_keluar', 50000, 195000, 145000,'Transfer ke Mega Wulandari',        'success','2025-05-15 14:30:00'),
('TRX-2025-00053',11,'pembayaran',      25000, 145000, 120000,'Bayar di Toko Alat Tulis Bu Ani',   'success','2025-05-16 08:30:00'),
('TRX-2025-00054',11,'pembayaran',      31000, 120000,  89000,'Bayar di Kantin Barokah',           'success','2025-05-17 11:00:00'),
-- Mega Wulandari (id 12)
('TRX-2025-00055',12,'transfer_masuk',  20000,  19000,  39000,'Terima dari Putri Amelia',          'success','2025-05-15 15:30:00'),
('TRX-2025-00056',12,'transfer_masuk',  50000,  39000,  89000,'Terima dari Gilang Ramadhan',       'success','2025-05-15 14:30:00'),
('TRX-2025-00057',12,'pembayaran',      15000,  89000,  74000,'Bayar di Kantin Barokah',           'success','2025-05-16 12:00:00'),
('TRX-2025-00058',12,'pembayaran',      10000,  74000,  64000,'Bayar di Koperasi Sekolah',         'success','2025-05-17 10:00:00'),
('TRX-2025-00059',12,'pembayaran',       8000,  64000,  56000,'Bayar di Toko Alat Tulis Bu Ani',   'success','2025-05-17 11:30:00'),
-- Budi Santoso / Guru (id 13)
('TRX-2025-00060',13,'topup',          500000, 250000, 750000,'Top Up via Mandiri',                'success','2025-05-05 07:30:00'),
('TRX-2025-00061',13,'pembayaran',      35000, 750000, 715000,'Bayar di Kantin Barokah',           'success','2025-05-06 11:30:00'),
('TRX-2025-00062',13,'pembayaran',      50000, 715000, 665000,'Bayar di Koperasi Sekolah',         'success','2025-05-09 12:00:00'),
('TRX-2025-00063',13,'transfer_keluar',100000, 665000, 565000,'Transfer ke Sari Dewi Utami',       'success','2025-05-11 10:00:00'),
('TRX-2025-00064',13,'qr_payment',      45000, 565000, 520000,'QR Pay di Kantin Barokah',          'success','2025-05-13 12:30:00'),
('TRX-2025-00065',13,'pembayaran',      30000, 520000, 490000,'Bayar di Toko Alat Tulis Bu Ani',   'success','2025-05-15 08:30:00'),
-- Sari Dewi Utami / Guru (id 14)
('TRX-2025-00066',14,'topup',          300000, 220000, 520000,'Top Up via BRI',                    'success','2025-05-06 08:45:00'),
('TRX-2025-00067',14,'transfer_masuk', 100000, 520000, 620000,'Terima dari Budi Santoso',          'success','2025-05-11 10:00:00'),
('TRX-2025-00068',14,'pembayaran',      40000, 620000, 580000,'Bayar di Kantin Barokah',           'success','2025-05-12 11:45:00'),
('TRX-2025-00069',14,'pembayaran',      35000, 580000, 545000,'Bayar di Koperasi Sekolah',         'success','2025-05-15 12:00:00'),
('TRX-2025-00070',14,'qr_payment',      25000, 545000, 520000,'QR Pay di Toko Alat Tulis Bu Ani',  'success','2025-05-17 08:00:00'),
-- Hendra Kusuma / Guru (id 15)
('TRX-2025-00071',15,'topup',          500000, 480000, 980000,'Top Up via BNI',                    'success','2025-05-07 09:00:00'),
('TRX-2025-00072',15,'pembayaran',      45000, 980000, 935000,'Bayar di Kantin Barokah',           'success','2025-05-08 11:30:00'),
('TRX-2025-00073',15,'pembayaran',      60000, 935000, 875000,'Bayar di Koperasi Sekolah',         'success','2025-05-10 12:00:00'),
('TRX-2025-00074',15,'qr_payment',      35000, 875000, 840000,'QR Pay di Kantin Barokah',          'success','2025-05-13 13:00:00'),
('TRX-2025-00075',15,'pembayaran',      40000, 840000, 800000,'Bayar di Toko Alat Tulis Bu Ani',   'success','2025-05-16 08:00:00'),
-- Dewi Susanti / Parent (id 16)
('TRX-2025-00076',16,'topup',          200000, 180000, 380000,'Top Up via BCA',                    'success','2025-05-08 10:30:00'),
('TRX-2025-00077',16,'transfer_keluar', 80000, 380000, 300000,'Transfer ke Ayu Lestari (anak)',    'success','2025-05-10 09:00:00'),
('TRX-2025-00078',16,'pembayaran',      30000, 300000, 270000,'Bayar di Koperasi Sekolah',         'success','2025-05-14 10:00:00'),
-- Agus Setiawan / Parent (id 17)
('TRX-2025-00079',17,'topup',          300000, 210000, 510000,'Top Up via BNI',                    'success','2025-05-12 10:00:00'),
('TRX-2025-00080',17,'transfer_keluar',100000, 510000, 410000,'Transfer ke Gilang Ramadhan (anak)','success','2025-05-13 08:30:00'),
('TRX-2025-00081',17,'pembayaran',      25000, 410000, 385000,'Bayar di Koperasi Sekolah',         'success','2025-05-16 09:30:00'),
-- Merchant pemasukan (id 18,19,20)
('TRX-2025-00082',18,'qr_payment',      25000,3225000,3250000,'Pembayaran dari Ahmad Fauzi',       'success','2025-05-02 10:05:00'),
('TRX-2025-00083',18,'qr_payment',      20000,3245000,3265000,'Pembayaran dari Rizky Pratama',     'success','2025-05-03 11:00:00'),
('TRX-2025-00084',18,'qr_payment',      35000,3205000,3240000,'Pembayaran dari Budi Santoso',      'success','2025-05-06 11:30:00'),
('TRX-2025-00085',19,'qr_payment',      25000,1850000,1875000,'Pembayaran dari Ahmad Fauzi',       'success','2025-05-09 08:30:00'),
('TRX-2025-00086',19,'qr_payment',      15000,1835000,1850000,'Pembayaran dari Nadia Safitri',     'success','2025-05-15 09:30:00'),
('TRX-2025-00087',20,'qr_payment',      35000,2065000,2100000,'Pembayaran dari Rizky Pratama',     'success','2025-05-08 08:45:00'),
('TRX-2025-00088',20,'qr_payment',      30000,2035000,2065000,'Pembayaran dari Nadia Safitri',     'success','2025-05-13 13:15:00');


-- =============================================
-- NOTIFICATIONS
-- =============================================
INSERT INTO notifications (user_id, judul, pesan, tipe, is_read, created_at) VALUES
(1,'Top Up Berhasil ✅',   'Top Up Rp 200.000 via BCA telah disetujui. Saldo bertambah!',              'topup',     1,'2025-05-01 09:15:00'),
(1,'Transfer Berhasil',    'Transfer Rp 50.000 ke Siti Rahmawati berhasil.',                            'transfer',  1,'2025-05-07 14:30:00'),
(1,'Pembayaran Berhasil',  'Pembayaran Rp 25.000 di Kantin Barokah berhasil diproses.',                 'pembayaran',1,'2025-05-02 10:05:00'),
(1,'🎉 Promo JAXPAY!',    'Cashback 10% untuk 10 transaksi pertama bulan Mei. Yuk belanja!',            'promo',     0,'2025-05-01 00:00:00'),
(1,'Top Up Ditolak ❌',   'Top Up Rp 500.000 ditolak. Bukti transfer tidak jelas/buram.',              'info',      0,'2025-04-20 10:00:00'),
(2,'Saldo Masuk 💰',       'Anda menerima transfer Rp 50.000 dari Ahmad Fauzi.',                        'transfer',  1,'2025-05-07 14:30:00'),
(2,'Pembayaran Berhasil',  'Pembayaran Rp 15.000 di Kantin Barokah berhasil.',                          'pembayaran',1,'2025-05-08 10:10:00'),
(2,'🎉 Promo JAXPAY!',    'Cashback 10% untuk 10 transaksi pertama bulan Mei. Yuk belanja!',            'promo',     0,'2025-05-01 00:00:00'),
(3,'Top Up Berhasil ✅',   'Top Up Rp 150.000 via Mandiri telah disetujui.',                           'topup',     1,'2025-05-02 10:20:00'),
(3,'Transfer Berhasil',    'Transfer Rp 50.000 ke Nadia Safitri berhasil.',                             'transfer',  1,'2025-05-10 13:15:00'),
(3,'Top Up Ditolak ❌',   'Top Up Rp 100.000 ditolak. Nominal tidak sesuai bukti.',                    'info',      1,'2025-04-25 14:00:00'),
(3,'🎉 Promo JAXPAY!',    'Cashback 10% untuk 10 transaksi pertama bulan Mei.',                         'promo',     0,'2025-05-01 00:00:00'),
(4,'Saldo Masuk 💰',       'Anda menerima transfer Rp 30.000 dari Ahmad Fauzi.',                        'transfer',  1,'2025-05-14 15:00:00'),
(4,'Pembayaran Berhasil',  'Pembayaran Rp 12.000 di Kantin Barokah berhasil.',                          'pembayaran',0,'2025-05-15 10:30:00'),
(5,'Top Up Berhasil ✅',   'Top Up Rp 500.000 via BNI telah disetujui. Saldo siap digunakan!',         'topup',     1,'2025-05-03 08:00:00'),
(5,'Transfer Berhasil',    'Transfer Rp 100.000 ke Bagas Kurniawan berhasil.',                          'transfer',  1,'2025-05-06 14:00:00'),
(5,'🎉 Promo JAXPAY!',    'Cashback 10% untuk 10 transaksi pertama bulan Mei.',                         'promo',     0,'2025-05-01 00:00:00'),
(6,'Top Up Berhasil ✅',   'Top Up Rp 100.000 via BSI telah disetujui.',                               'topup',     1,'2025-05-09 11:00:00'),
(6,'Transfer Berhasil',    'Transfer Rp 20.000 ke Mega Wulandari berhasil.',                            'transfer',  0,'2025-05-15 15:30:00'),
(7,'Saldo Masuk 💰',       'Anda menerima transfer Rp 25.000 dari Siti Rahmawati.',                     'transfer',  0,'2025-05-15 16:00:00'),
(7,'🎉 Promo JAXPAY!',    'Cashback 10% untuk transaksi di merchant JAXPAY bulan Mei!',                 'promo',     0,'2025-05-01 00:00:00'),
(8,'Top Up Berhasil ✅',   'Top Up Rp 150.000 via Mandiri telah disetujui.',                           'topup',     1,'2025-05-10 08:30:00'),
(8,'Saldo Masuk 💰',       'Anda menerima transfer Rp 50.000 dari Rizky Pratama.',                      'transfer',  1,'2025-05-10 13:15:00'),
(8,'🎉 Promo JAXPAY!',    'Cashback 10% untuk transaksi di merchant JAXPAY bulan Mei!',                 'promo',     0,'2025-05-01 00:00:00'),
(9,'Top Up Berhasil ✅',   'Top Up Rp 300.000 via BCA telah disetujui.',                               'topup',     1,'2025-05-04 11:00:00'),
(9,'Saldo Masuk 💰',       'Anda menerima transfer Rp 100.000 dari Deni Firmansyah.',                   'transfer',  1,'2025-05-06 14:00:00'),
(9,'Transfer Berhasil',    'Transfer Rp 75.000 ke Indah Permatasari berhasil.',                         'transfer',  0,'2025-05-14 14:00:00'),
(10,'Saldo Masuk 💰',      'Anda menerima transfer Rp 75.000 dari Bagas Kurniawan.',                    'transfer',  0,'2025-05-14 14:00:00'),
(10,'🎉 Promo JAXPAY!',   'Cashback 10% untuk 10 transaksi pertama bulan Mei.',                         'promo',     0,'2025-05-01 00:00:00'),
(11,'Top Up Berhasil ✅',  'Top Up Rp 250.000 via BCA telah disetujui.',                               'topup',     1,'2025-05-11 09:45:00'),
(11,'Transfer Berhasil',   'Transfer Rp 50.000 ke Mega Wulandari berhasil.',                            'transfer',  0,'2025-05-15 14:30:00'),
(12,'Saldo Masuk 💰',      'Anda menerima transfer Rp 20.000 dari Putri Amelia.',                       'transfer',  0,'2025-05-15 15:30:00'),
(12,'Saldo Masuk 💰',      'Anda menerima transfer Rp 50.000 dari Gilang Ramadhan.',                    'transfer',  0,'2025-05-15 14:30:00'),
(12,'🎉 Promo JAXPAY!',   'Cashback 10% untuk 10 transaksi pertama bulan Mei.',                         'promo',     0,'2025-05-01 00:00:00'),
(13,'Top Up Berhasil ✅',  'Top Up Rp 500.000 via Mandiri telah disetujui.',                           'topup',     1,'2025-05-05 07:30:00'),
(13,'Transfer Berhasil',   'Transfer Rp 100.000 ke Sari Dewi Utami berhasil.',                          'transfer',  1,'2025-05-11 10:00:00'),
(13,'🔔 Info Sistem',      'Fitur QR Payment kini tersedia! Scan QR merchant untuk bayar lebih mudah.', 'info',      1,'2025-05-01 00:00:00'),
(14,'Top Up Berhasil ✅',  'Top Up Rp 300.000 via BRI telah disetujui.',                               'topup',     1,'2025-05-06 08:45:00'),
(14,'Saldo Masuk 💰',      'Anda menerima transfer Rp 100.000 dari Budi Santoso.',                      'transfer',  1,'2025-05-11 10:00:00'),
(14,'🎉 Promo JAXPAY!',   'Cashback 10% untuk transaksi di merchant JAXPAY bulan Mei!',                 'promo',     0,'2025-05-01 00:00:00'),
(15,'Top Up Berhasil ✅',  'Top Up Rp 500.000 via BNI telah disetujui.',                               'topup',     1,'2025-05-07 09:00:00'),
(15,'🎉 Promo JAXPAY!',   'Cashback 10% untuk transaksi di merchant JAXPAY bulan Mei!',                 'promo',     0,'2025-05-01 00:00:00'),
(16,'Top Up Berhasil ✅',  'Top Up Rp 200.000 via BCA telah disetujui.',                               'topup',     1,'2025-05-08 10:30:00'),
(16,'Transfer Berhasil',   'Transfer Rp 80.000 ke Ayu Lestari berhasil dikirim.',                       'transfer',  1,'2025-05-10 09:00:00'),
(16,'🔔 Info Sistem',      'Fitur notifikasi real-time kini aktif di JAXPAY!',                          'info',      0,'2025-05-01 00:00:00'),
(17,'Top Up Berhasil ✅',  'Top Up Rp 300.000 via BNI telah disetujui.',                               'topup',     1,'2025-05-12 10:00:00'),
(17,'Transfer Berhasil',   'Transfer Rp 100.000 ke Gilang Ramadhan berhasil dikirim.',                  'transfer',  0,'2025-05-13 08:30:00'),
(17,'🎉 Promo JAXPAY!',   'Cashback 10% untuk transaksi di merchant JAXPAY bulan Mei!',                 'promo',     0,'2025-05-01 00:00:00');


-- =============================================
-- ACTIVITY LOGS
-- =============================================
INSERT INTO activity_logs (user_type, user_id, aksi, detail, ip_address, created_at) VALUES
('admin',1,'Login Admin',    'Super Admin login ke dashboard',                                  '127.0.0.1',    '2025-05-01 08:00:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025001 — Ahmad Fauzi — Rp 200.000 via BCA',        '127.0.0.1',    '2025-05-01 09:15:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025002 — Rizky Pratama — Rp 150.000 via Mandiri', '127.0.0.1',    '2025-05-02 10:20:00'),
('admin',1,'Reject Top Up',  'Reject TPU-2025018 — Bukti transfer tidak jelas/buram',          '127.0.0.1',    '2025-04-20 10:00:00'),
('admin',1,'Reject Top Up',  'Reject TPU-2025019 — Nominal tidak sesuai bukti transfer',       '127.0.0.1',    '2025-04-25 14:00:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025003 — Deni Firmansyah — Rp 500.000 via BNI',   '127.0.0.1',    '2025-05-03 08:00:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025004 — Bagas Kurniawan — Rp 300.000 via BCA',   '127.0.0.1',    '2025-05-04 11:00:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025005 — Budi Santoso — Rp 500.000 via Mandiri',  '127.0.0.1',    '2025-05-05 07:30:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025006 — Sari Dewi Utami — Rp 300.000 via BRI',   '127.0.0.1',    '2025-05-06 08:45:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025007 — Hendra Kusuma — Rp 500.000 via BNI',     '127.0.0.1',    '2025-05-07 09:00:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025008 — Dewi Susanti — Rp 200.000 via BCA',      '127.0.0.1',    '2025-05-08 10:30:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025009 — Putri Amelia — Rp 100.000 via BSI',      '127.0.0.1',    '2025-05-09 11:00:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025010 — Nadia Safitri — Rp 150.000 via Mandiri', '127.0.0.1',    '2025-05-10 08:30:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025011 — Gilang Ramadhan — Rp 250.000 via BCA',   '127.0.0.1',    '2025-05-11 09:45:00'),
('admin',1,'Approve Top Up', 'Approve TPU-2025012 — Agus Setiawan — Rp 300.000 via BNI',     '127.0.0.1',    '2025-05-12 10:00:00'),
('admin',1,'Login Admin',    'Super Admin login ke dashboard',                                  '127.0.0.1',    '2025-05-17 06:30:00'),
('user', 1,'Login',          'Ahmad Fauzi login via OTP Email',                                '192.168.1.10', '2025-05-01 08:45:00'),
('user', 1,'Transfer',       'Transfer Rp 50.000 ke Siti Rahmawati (JAX-2022002)',             '192.168.1.10', '2025-05-07 14:30:00'),
('user', 1,'QR Payment',     'QR Pay Rp 25.000 di Toko Alat Tulis Bu Ani',                    '192.168.1.10', '2025-05-09 08:30:00'),
('user', 2,'Login',          'Siti Rahmawati login via OTP Email',                             '192.168.1.11', '2025-05-08 09:50:00'),
('user', 2,'Transfer',       'Transfer Rp 25.000 ke Farhan Maulana (JAX-2022007)',             '192.168.1.11', '2025-05-15 16:00:00'),
('user', 3,'Login',          'Rizky Pratama login via OTP Email',                              '192.168.1.12', '2025-05-02 09:00:00'),
('user', 3,'Transfer',       'Transfer Rp 50.000 ke Nadia Safitri (JAX-2022008)',              '192.168.1.12', '2025-05-10 13:15:00'),
('user', 5,'Login',          'Deni Firmansyah login via OTP Email',                            '192.168.1.14', '2025-05-03 07:30:00'),
('user', 5,'Transfer',       'Transfer Rp 100.000 ke Bagas Kurniawan (JAX-2022009)',           '192.168.1.14', '2025-05-06 14:00:00'),
('user', 9,'Login',          'Bagas Kurniawan login via OTP Email',                            '192.168.1.18', '2025-05-04 10:30:00'),
('user', 9,'Transfer',       'Transfer Rp 75.000 ke Indah Permatasari (JAX-2022010)',          '192.168.1.18', '2025-05-14 14:00:00'),
('user',13,'Login',          'Budi Santoso login via OTP Email',                               '192.168.1.21', '2025-05-05 07:00:00'),
('user',13,'Transfer',       'Transfer Rp 100.000 ke Sari Dewi Utami (JAX-GTK002)',            '192.168.1.21', '2025-05-11 10:00:00'),
('user',16,'Login',          'Dewi Susanti login via OTP Email',                               '192.168.1.25', '2025-05-08 09:00:00'),
('user',16,'Transfer',       'Transfer Rp 80.000 ke Ayu Lestari (JAX-2022004)',                '192.168.1.25', '2025-05-10 09:00:00'),
('user',17,'Login',          'Agus Setiawan login via OTP Email',                              '192.168.1.26', '2025-05-12 09:00:00'),
('user',17,'Transfer',       'Transfer Rp 100.000 ke Gilang Ramadhan (JAX-2022011)',           '192.168.1.26', '2025-05-13 08:30:00');

