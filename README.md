# Task Manager CMS

Sistem manajemen task yang dibuat dengan PHP Native dan Tailwind CSS. Aplikasi ini memiliki dua role utama: **Manager** dan **User**.

## Fitur Utama

### Manager

- Dashboard dengan statistik lengkap
- Membuat dan mengelola tasks
- Mengelola users
- Melihat semua tasks dengan berbagai filter
- Mengupdate status tasks
- Laporan dan analisis

### User

- Dashboard personal dengan tasks yang ditugaskan
- Melihat dan mengupdate status tasks sendiri
- Filter tasks berdasarkan status dan prioritas
- Riwayat tasks yang telah diselesaikan
- Notification untuk deadline

## Teknologi

- **Backend**: PHP 7.4+ (Native PHP, tanpa framework)
- **Frontend**: HTML5, CSS3 dengan Tailwind CSS
- **Database**: MySQL 5.7+
- **JavaScript**: Vanilla JS untuk interaksi dinamis
- **Icons**: Font Awesome 6

## Instalasi

### 1. Requirements

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)

### 2. Setup Database

1. Buat database baru dengan nama `taskmanager_cms`
2. Import file `database.sql` ke database yang sudah dibuat
3. Sesuaikan konfigurasi database di `config/database.php`

### 3. Setup File

1. Clone atau download project ini
2. Letakkan di direktori web server (htdocs, www, atau public_html)
3. Pastikan permission folder `uploads/` dan `logs/` dapat ditulis

### 4. Konfigurasi

Edit file `config/database.php` untuk menyesuaikan setting database:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'taskmanager_cms');
```

## Akun Default

Setelah setup database, Anda dapat login dengan akun berikut:

### Manager

- **Username**: manager
- **Password**: password123

### User

- **Username**: john_doe
- **Password**: password123

- **Username**: jane_smith
- **Password**: password123

## Struktur File

```
taskmanager-cms/
├── config/
│   ├── database.php      # Konfigurasi database
│   └── session.php       # Manajemen session
├── includes/
│   ├── header.php        # Header template
│   └── footer.php        # Footer template
├── ajax/
│   └── update-task-status.php  # AJAX handler
├── assets/
│   ├── css/             # Custom CSS (jika ada)
│   ├── js/              # Custom JavaScript
│   └── images/          # Gambar dan assets
├── uploads/             # Folder upload file
├── logs/                # Log aplikasi
├── database.sql         # SQL schema dan data sample
├── index.php           # Halaman utama (redirect)
├── login.php           # Halaman login
├── logout.php          # Logout handler
├── manager-dashboard.php # Dashboard manager
├── user-dashboard.php   # Dashboard user
├── create-task.php     # Form buat task baru
├── all-tasks.php       # Daftar semua tasks (manager)
├── my-tasks.php        # Tasks user (user)
├── manage-users.php    # Manajemen users (manager)
└── README.md           # Dokumentasi ini
```

## Penggunaan

### Sebagai Manager

1. Login dengan akun manager
2. Dari dashboard, Anda dapat:
   - Melihat overview statistik tasks dan users
   - Membuat task baru melalui "Buat Task Baru"
   - Mengelola semua tasks di "Semua Tasks"
   - Mengelola users di "Kelola Users"
   - Melihat laporan di "Laporan"

### Sebagai User

1. Login dengan akun user
2. Dari dashboard, Anda dapat:
   - Melihat tasks yang ditugaskan kepada Anda
   - Mengupdate status tasks
   - Melihat deadline dan prioritas
   - Mengakses riwayat tasks yang telah diselesaikan

## API Endpoints

### AJAX Endpoints

- `POST /ajax/update-task-status.php` - Update status task
- `POST /ajax/delete-task.php` - Hapus task
- `POST /ajax/delete-user.php` - Hapus user
- `POST /ajax/reset-password.php` - Reset password user

## Database Schema

### Table: users

- id (INT, PRIMARY KEY)
- username (VARCHAR, UNIQUE)
- email (VARCHAR, UNIQUE)
- password (VARCHAR, hashed)
- full_name (VARCHAR)
- role (ENUM: 'user', 'manager')
- status (ENUM: 'active', 'inactive')
- created_at, updated_at (TIMESTAMP)

### Table: tasks

- id (INT, PRIMARY KEY)
- title (VARCHAR)
- description (TEXT)
- status (ENUM: 'pending', 'in_progress', 'completed', 'cancelled')
- priority (ENUM: 'low', 'medium', 'high', 'urgent')
- assigned_to (INT, FK to users.id)
- created_by (INT, FK to users.id)
- due_date (DATE)
- completed_at (TIMESTAMP)
- created_at, updated_at (TIMESTAMP)

## Security Features

- Session-based authentication
- Password hashing menggunakan PHP `password_hash()`
- SQL injection protection dengan prepared statements
- XSS protection dengan `htmlspecialchars()`
- Role-based access control
- CSRF protection (dapat ditambahkan)

## Pengembangan Lanjutan

Fitur yang dapat ditambahkan:

- Upload file attachment untuk tasks
- Email notification
- Real-time notification dengan WebSocket
- Export laporan ke PDF/Excel
- Calendar view untuk tasks
- Task templates
- Time tracking
- Task dependencies

## Troubleshooting

### Database Connection Error

- Pastikan MySQL service berjalan
- Cek kredensial database di `config/database.php`
- Pastikan database `taskmanager_cms` sudah dibuat

### Permission Denied

- Pastikan folder `uploads/` dan `logs/` memiliki permission 755 atau 777
- Cek ownership file dan folder

### Session Issues

- Pastikan PHP session berjalan normal
- Cek setting `session.save_path` di php.ini

## License

Project ini dibuat untuk tujuan edukasi dan dapat digunakan secara bebas.

## Support

Jika mengalami masalah atau membutuhkan bantuan, silakan buat issue di repository ini.
