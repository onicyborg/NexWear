# NexWear

NexWear adalah aplikasi internal untuk manajemen proses produksi (Cutting, Sewing, Quality Control) dan administrasi PO.

## Requirements

- PHP 8.1+
- Composer
- PostgreSQL (dibutuhkan karena ada kolom `jsonb` di migration)
- Git

## Setup (Local)

1. Clone repository

```bash
git clone <REPO_URL>
cd NexWear
```

2. Install dependencies (Composer)

```bash
composer install
```

3. Buat file environment

```bash
cp .env.example .env
```

4. Set konfigurasi database di `.env`

Contoh (PostgreSQL):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nexwear
DB_USERNAME=postgres
DB_PASSWORD=
```

5. Generate APP key

```bash
php artisan key:generate
```

6. Migrate + seed data

```bash
php artisan migrate --seed
```

Jika ingin reset total database (opsional):

```bash
php artisan migrate:fresh --seed
```

7. Buat storage symlink (wajib untuk file upload, contoh: foto profil)

```bash
php artisan storage:link
```

8. Jalankan server

```bash
php artisan serve
```

Akses aplikasi:

- http://127.0.0.1:8000

## Default Accounts (Seeder)

Password semua user:

- `Qwerty123*`

Akun:

- Admin: `admin@nexwear.com`
- Cutting: `cutting@nexwear.com`
- Sewing: `sewing@nexwear.com`
- QC: `qc@nexwear.com`

## Notes

- Project ini **tidak membutuhkan NPM/Node setup** untuk menjalankan di local.
- Jika setelah ubah `.env` ada konfigurasi yang belum kebaca, jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```
