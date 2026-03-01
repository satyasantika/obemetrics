# obemetrics

`obemetrics` adalah aplikasi berbasis web untuk mendukung pengelolaan data dan evaluasi Outcome-Based Education (OBE), seperti pemetaan CPL, CPMK, kurikulum, evaluasi, serta rekap data akademik terkait.

---

## 1. Judul dan Deskripsi Singkat Aplikasi

**Nama Aplikasi:** `obemetrics`  
**Tujuan:** mempermudah pengelolaan proses OBE secara terstruktur, terdokumentasi, dan terukur dalam satu sistem terpusat.

---

## 2. Fitur Utama

Berikut fitur utama yang tersedia (atau direncanakan) pada `obemetrics`:

- Manajemen data master akademik (program studi, mata kuliah, semester, mahasiswa).
- Pengelolaan CPL, CPMK, Sub-CPMK, dan relasi antar komponen OBE.
- Evaluasi dan pemantauan capaian pembelajaran.
- Dukungan tabel data interaktif (filter, sortir, ekspor) melalui DataTables.
- Manajemen pengguna, role, dan permission.
- Dukungan autentikasi berbasis Laravel + Sanctum untuk endpoint API.

---

## 3. Teknologi yang Digunakan

### Backend
- PHP `^8.1`
- Laravel `^10.10`
- MySQL/MariaDB (disarankan; sesuaikan dengan konfigurasi `.env`)

### Frontend & Build Tools
- Vite `^5.0.0`
- Bootstrap `^5.2.3`
- Sass `^1.56.1`
- Axios `^1.6.4`

### Library Pendukung
- `yajra/laravel-datatables`
- `spatie/laravel-permission`
- `laravel/sanctum`
- `phpoffice/phpspreadsheet`
- `lab404/laravel-impersonate`

---

## 4. Cara Instalasi

1. Clone repository:

	```bash
	git clone <URL_REPOSITORY_ANDA>
	cd obemetrics
	```

2. Install dependency backend (Composer):

	```bash
	composer install
	```

3. Install dependency frontend (NPM):

	```bash
	npm install
	```

4. Buat file environment:

	```bash
	cp .env.example .env
	```

	> Untuk Windows CMD, gunakan:
	> ```cmd
	> copy .env.example .env
	> ```

5. Generate application key:

	```bash
	php artisan key:generate
	```

6. Atur konfigurasi database di `.env`, lalu jalankan migrasi:

	```bash
	php artisan migrate
	```

7. (Opsional) Jalankan seeder jika tersedia:

	```bash
	php artisan db:seed
	```

---

## 5. Cara Menjalankan Aplikasi

1. Jalankan server Laravel:

	```bash
	php artisan serve
	```

2. Jalankan Vite dev server (terminal terpisah):

	```bash
	npm run dev
	```

3. Akses aplikasi di browser:

	```
	http://127.0.0.1:8000
	```

### Mode Production (build asset)

```bash
npm run build
```

---

## 6. Contoh Konfigurasi

Contoh minimal konfigurasi `.env`:

```env
APP_NAME=obemetrics
APP_ENV=local
APP_KEY=base64:GENERATED_KEY
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=obemetrics
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

> Placeholder di atas dapat disesuaikan dengan environment server Anda.

---

## 7. Struktur Folder/Proyek

Struktur utama proyek:

```text
obemetrics/
├── app/
│   ├── Console/
│   ├── DataTables/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Providers/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   ├── sass/
│   └── views/
├── routes/
│   ├── web.php
│   └── api.php
├── storage/
├── tests/
├── composer.json
├── package.json
└── README.md
```

---

## 8. Screenshot atau Demo

Tambahkan dokumentasi visual aplikasi di bagian ini:

- Screenshot Dashboard: `![Dashboard](docs/screenshots/dashboard.png)`
- Screenshot Modul Evaluasi: `![Evaluasi](docs/screenshots/evaluasi.png)`
- Demo Video: `[Tautan Demo](https://supportfkip.unsil.ac.id/demo-obemetrics)`

---

## 9. API Endpoint

Endpoint API yang sudah terdeteksi:

| Method | Endpoint | Middleware | Keterangan |
|---|---|---|---|
| GET | `/api/user` | `auth:sanctum` | Mengambil data user terautentikasi |

Template endpoint tambahan (isi sesuai implementasi Anda):

| Method | Endpoint | Middleware | Keterangan |
|---|---|---|---|
| GET | `/api/<resource>` | `auth:sanctum` | List data |
| POST | `/api/<resource>` | `auth:sanctum` | Tambah data |
| PUT/PATCH | `/api/<resource>/{id}` | `auth:sanctum` | Ubah data |
| DELETE | `/api/<resource>/{id}` | `auth:sanctum` | Hapus data |

---

## 10. Lisensi

Proyek ini menggunakan lisensi **MIT**.  
Silakan lihat detail lisensi pada file `LICENSE` (jika belum ada, dapat ditambahkan kemudian).

---

## 11. Kontributor

```text
Nama: <satya santika>
Peran: <pengembang>
Kontak: <satyasantika@unsil.ac.id>
```

Kami sangat terbuka jika ada yang bersedia bergabung pada proyek ini

---

## 12. Catatan Tambahan / Roadmap Versi Selanjutnya

Rencana pengembangan berikutnya:

- [ ] Menambahkan dokumentasi API lengkap (OpenAPI/Swagger).
- [ ] Menambahkan pengujian otomatis untuk modul inti.
- [ ] Menambahkan dashboard analitik OBE yang lebih komprehensif.
- [ ] Integrasi ekspor laporan ke format PDF/Excel yang lebih fleksibel.
- [ ] Menyusun panduan deployment production (Nginx/Apache + queue + scheduler).

Catatan tambahan:

- Pastikan konfigurasi `.env` tidak dibagikan ke publik.
- Gunakan branch terpisah untuk pengembangan fitur baru.
- Lakukan backup database secara berkala.

---

## Informasi Kontak Proyek (Placeholder)

- Email tim: `<comming soon>`
- Issue tracker: `<comming soon>`
- Dokumentasi internal: `<comming soon>`
