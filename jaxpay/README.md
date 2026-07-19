# ⚡ JAXPAY — School Digital Wallet System

> Aplikasi dompet digital sekolah berbasis PHP Native + MySQL  
> Mobile App UI untuk User · Web Dashboard untuk Admin

---

## 📦 Requirement

| Kebutuhan | Versi         |
| --------- | ------------- |
| PHP       | >= 7.4        |
| MySQL     | >= 5.7        |
| XAMPP     | >= 8.x        |
| Browser   | Chrome / Edge |

---

## 🚀 Cara Instalasi (XAMPP)

### 1. Copy Project

```
Salin folder jaxpay/ ke:
C:\xampp\htdocs\jaxpay\
```

### 2. Import Database

```
1. Buka http://localhost/phpmyadmin
2. Klik "New" → buat database: jaxpay_db
3. Pilih database jaxpay_db → tab "Import"
4. Upload file: database/jaxpay.sql
5. Klik "Go"
```

### 3. Konfigurasi Koneksi

Edit file `koneksi.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // kosong jika XAMPP default
define('DB_NAME', 'jaxpay_db');
```

### 4. Konfigurasi Email OTP (Opsional)

Edit bagian SMTP di `koneksi.php`:

```php
define('SMTP_USER', 'emailkamu@gmail.com');
define('SMTP_PASS', 'xxxx xxxx xxxx xxxx');  // App Password Gmail
```

> **Cara dapat App Password Gmail:**
>
> 1. Login Google Account → Security
> 2. Aktifkan 2-Step Verification
> 3. App Passwords → buat password baru
> 4. Pilih "Mail" dan "Windows Computer"

### 5. Install PHPMailer (jika pakai email)

Jika Composer tersedia:

```bash
cd jaxpay/
composer require phpmailer/phpmailer
```

Jika Composer belum tersedia, download manual:

- https://github.com/PHPMailer/PHPMailer
- letakkan folder `phpmailer` di `jaxpay/vendor/phpmailer/`

Setelah itu, Script `proses/kirim_otp.php` akan menggunakan PHPMailer secara otomatis.

### 6. Permission Folder Upload

```
Pastikan folder berikut bisa ditulis (writable):
- assets/uploads/topup/
- assets/uploads/profile/
```

### 7. Akses Aplikasi

```
User App  : http://localhost/jaxpay/
Admin     : http://localhost/jaxpay/admin/
```

---

## 🔐 Default Login

### Admin Dashboard

```
URL      : http://localhost/jaxpay/admin/
Username : admin
Password : password
```

### User App (Demo Accounts)

```
Email: ahmad@student.jaxpay.id     → Student (Saldo: Rp 150.000)
Email: siti@student.jaxpay.id      → Student (Saldo: Rp 75.000)
Email: budi@guru.jaxpay.id         → Teacher (Saldo: Rp 500.000)
Email: dewi@parent.jaxpay.id       → Parent  (Saldo: Rp 250.000)
Email: kantin@merchant.jaxpay.id   → Merchant (Saldo: Rp 1.200.000)
```

> 📌 **DEMO MODE**: Jika SMTP belum dikonfigurasi, kode OTP akan tampil di response JSON.  
> Buka DevTools → Network → Request `kirim_otp.php` → cek field `demo_otp`.

---

## 📁 Struktur Project

```
jaxpay/
├── index.php              # Entry point (redirect)
├── koneksi.php            # DB + SMTP config
├── .htaccess              # Security rules
│
├── auth/                  # Login & OTP
│   ├── login.php
│   ├── otp.php
│   ├── verify_otp.php
│   ├── logout.php
│   └── session.php
│
├── halaman/               # User Mobile App Pages
│   ├── home.php           # Dashboard utama
│   ├── transfer.php       # Transfer saldo
│   ├── topup.php          # Top up saldo
│   ├── qr.php             # Generate QR
│   ├── scan.php           # Scan QR merchant
│   ├── pembayaran.php     # Konfirmasi bayar
│   ├── mutasi.php         # Riwayat transaksi
│   ├── merchant.php       # Daftar merchant
│   ├── notifikasi.php     # Notifikasi
│   ├── profile.php        # Profil user
│   ├── settings.php       # Edit profil
│   └── detail_transaksi.php
│
├── admin/                 # Admin Web Dashboard
│   ├── index.php          # Login admin
│   ├── dashboard.php      # Overview + chart
│   ├── users.php          # Kelola user
│   ├── topup.php          # Konfirmasi top up
│   ├── transaksi.php      # Semua transaksi
│   ├── merchant.php       # Kelola merchant
│   ├── laporan.php        # Laporan bulanan
│   ├── settings.php       # Pengaturan admin
│   ├── sidebar.php        # Component sidebar
│   ├── navbar.php         # Component topbar
│   └── footer.php
│
├── proses/                # Backend logic
│   ├── kirim_otp.php
│   ├── verify_otp.php
│   ├── transfer.php
│   ├── topup.php
│   ├── pembayaran.php
│   ├── qr_payment.php
│   ├── admin_confirm.php
│   ├── update_profile.php
│   ├── mark_notif.php
│   └── qr_payment.php
│
├── assets/
│   ├── css/
│   │   ├── mobile.css     # User app styles
│   │   └── admin.css      # Admin dashboard styles
│   ├── js/
│   │   ├── app.js         # Splash + animations
│   │   ├── otp.js
│   │   ├── transfer.js
│   │   ├── qr.js
│   │   ├── merchant.js
│   │   ├── admin.js
│   │   └── chart.js
│   └── uploads/
│       ├── topup/         # Bukti transfer
│       └── profile/       # Foto profil
│
└── database/
    └── jaxpay.sql         # Schema + dummy data
```

---

## ✨ Fitur Lengkap

### User (Mobile App — iPhone 17 Pro Max Style)

- [x] Login via OTP Email (6 digit, expired 5 menit)
- [x] Dashboard dengan saldo & animasi counter
- [x] Splash screen animasi
- [x] Top Up dengan upload bukti transfer
- [x] Transfer antar pengguna
- [x] QR Payment (generate & scan)
- [x] Bayar ke merchant
- [x] Riwayat transaksi (filter tipe)
- [x] Detail transaksi
- [x] Notifikasi real-time
- [x] Edit profil & foto
- [x] Bottom navigation
- [x] Promo banner
- [x] Balance hide/show toggle
- [x] Ripple effect & smooth transitions
- [x] Member level (Basic/Silver/Gold/Platinum)

### Admin (Full Web Dashboard)

- [x] Login admin (username + password)
- [x] Dashboard dengan Chart.js (line + doughnut)
- [x] Grafik transaksi 7 hari
- [x] Kelola user (CRUD, reset saldo)
- [x] Konfirmasi Top Up (approve/reject + lihat bukti)
- [x] Semua Transaksi (filter, pagination)
- [x] Manajemen Merchant
- [x] Laporan bulanan
- [x] Log aktivitas sistem
- [x] Pengaturan admin (ganti profil & password)
- [x] Responsive sidebar
- [x] Dark theme professional

---

## 🛡️ Keamanan

- Session-based authentication
- OTP expire 5 menit & single-use
- SQL Injection prevention (prepared statements)
- File upload validation (type + size)
- Role-based access control
- XSS protection (htmlspecialchars)

---

## 📝 Catatan Demo

> Untuk presentasi/demo tanpa koneksi email, gunakan mode demo:
>
> 1. Login dengan email demo di atas
> 2. Klik "Kirim OTP"
> 3. Buka DevTools (F12) → Network → klik request `kirim_otp.php`
> 4. Di response JSON akan ada field `demo_otp` berisi kode OTP 6 digit
> 5. Masukkan kode tersebut di halaman OTP

---

**JAXPAY v1.0** · Dibuat dengan ❤️ untuk kemudahan transaksi di lingkungan sekolah
