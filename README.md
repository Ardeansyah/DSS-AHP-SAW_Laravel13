# SPK Hybrid AHP–SAW (Laravel)

Aplikasi Sistem Pendukung Keputusan (SPK) berbasis Laravel yang mengintegrasikan metode Analytical Hierarchy Process (AHP) dan Simple Additive Weighting (SAW) dengan alur validasi 5 Milestone.

---

## 1. Persiapan Lingkungan (Prerequisites)

Proyek ini menggunakan database SQLite berbasis file lokal sehingga dapat langsung dijalankan tanpa Docker maupun instalasi server MySQL terpisah. Pastikan perangkat Anda sudah terpasang:

* PHP >= 8.2 (ekstensi aktif: sqlite3, pdo_sqlite, mbstring, xml, zip, curl, fileinfo)
* Composer >= 2.6
* Git

Rekomendasi paket instalasi cepat:
* Windows: Laravel Herd (https://herd.laravel.com/windows) atau Laragon (https://laragon.org/download/)
* macOS: Laravel Herd (https://herd.laravel.com/) atau `brew install php composer git`
* Linux (Ubuntu/Debian): `sudo apt update && sudo apt install -y php php-cli php-sqlite3 php-mbstring php-xml php-zip php-curl composer git`

---

## 2. Prosedur Menjalankan Project dari 0

1. Clone repositori dan masuk ke direktori proyek:
   git clone <URL_REPOSITORI> spk-ahp-saw
   cd spk-ahp-saw

2. Instal seluruh dependensi PHP:
   composer install --ignore-platform-req=ext-gd --ignore-platform-req=ext-iconv

3. Salin file konfigurasi environment dan generate application key:
   cp .env.example .env
   php artisan key:generate

4. Pastikan koneksi database di file `.env` diatur ke SQLite (hapus atau beri tanda `#` pada konfigurasi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` jika ada):
   DB_CONNECTION=sqlite

5. Buat file database SQLite kosong:
   * Linux / macOS / Git Bash:
     touch database/database.sqlite
   * Windows CMD:
     type nul > database\database.sqlite

6. Jalankan migrasi tabel dan seeder data awal:
   php artisan migrate:fresh --seed

7. Jalankan server lokal:
   php artisan serve

8. Buka browser dan akses:
   http://127.0.0.1:8000
