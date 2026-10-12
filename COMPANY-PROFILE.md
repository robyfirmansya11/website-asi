# Company profile Artabumi

Preview: `php artisan serve --host=127.0.0.1 --port=8000`.
If the local PHP runtime has no SQLite driver, use process-local file storage:
```powershell
$env:SESSION_DRIVER='file'
$env:CACHE_STORE='file'
php artisan serve --host=127.0.0.1 --port=8000
```

Public pages: `/` (Indonesian), `/id`, `/en`, `/zh` (Simplified Chinese).
News summaries: `/{locale}/news/0` through `/{locale}/news/2`.
Edit localized copy and archive summaries in `lang/{locale}/company.php`.
Photos, logo, fonts, stylesheet and gallery script are local in `public/company`.
This initial version does not include an editorial CMS. Existing authenticated starter pages are preserved.

Source: https://www.artabumisentraindustri.com, retrieved 1 October 2026.
Company logo, facility photographs, office address, email, phone, operating-since year and archive context come from that website. News summaries have been rewritten and translated; publication dates are archival, not current project-status claims. Values descriptions and introductory headings are proposed editorial copy. Confirm company facts and approve translations before publication. The old website contained unrelated gambling links; none were migrated. The existing public website has not been changed.

Validation:
```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/pest --compact tests/Feature/CompanyTest.php tests/Feature/ExampleTest.php
```
The extension flags are only needed on the current CLI installation, where SQLite is installed but not enabled by default.

## Careers / HR

- Public careers: `/id/careers`, `/en/careers`, `/zh/careers`.
- HR management: `/hr/vacancies`; requires login, verified email, and HR authorization.
- `recruitment@artabumisentraindustri.com` is explicitly authorized in `config/careers.php`. The account does not exist yet: register at `/register`, verify ownership of the email, then visit `/hr/vacancies`. No account password was generated or email verification bypassed.
- Current mail driver is `log`. Configure a real SMTP/mail provider for email verification and password resets before HR onboarding in production. Local verification messages are recorded in the Laravel log; they are not delivered.
- Additional HR accounts can be granted access with `php artisan careers:hr account@example.com` after registration. `--revoke` removes the database role; configured accounts must first be removed from `config/careers.php`.
- HR can create, edit, draft, publish and close vacancies. Indonesian title, description and requirements are mandatory; English and Chinese translations are optional but must be complete when provided. Missing translations fall back to Indonesian with a visible notice.
- Application delivery uses a `mailto:` link to the email specified by HR (default recruitment address); there is no uploaded CV collection or applicant tracking system.
- Closing dates include the selected day in Asia/Jakarta. Draft, closed and expired positions cannot be opened through public detail URLs.
- No sample vacancies were inserted into the real local database.
- Run `php artisan migrate` on deployment. Use HTTPS and configure mail and database credentials on the hosting environment.

The local PHP server needs SQLite enabled for careers. Start it from `public` with:
```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 -S 127.0.0.1:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
```
Validation for careers and existing company pages:
```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/pest --compact tests/Feature/CareerTest.php tests/Feature/CompanyTest.php tests/Feature/ExampleTest.php
```

## Berita & Instagram

Backend: `/hr/articles` (akun HR terverifikasi yang sama dengan pengelolaan Karir). Beranda menampilkan tiga artikel terbit terbaru berdasarkan tanggal publikasi. Tambah/edit mencakup gambar sampul maksimal 5 MB (JPG/PNG/WebP), judul, ringkasan, isi, dan terjemahan ID/EN/ZH. Artikel draf tidak dapat dibuka dari URL publik. Tiga arsip lama sudah disalin ke database melalui `ArticleSeeder`; seeder tidak menambahkan data jika tabel artikel sudah berisi. URL arsip lama tetap tersedia.

Instagram belum tersambung. Tidak ada postingan contoh yang dianggap sebagai postingan asli.

Langkah aktivasi integrasi:
1. Siapkan akun Instagram Business/Creator dan aplikasi Meta dengan Instagram API with Instagram Login. Berikan akses pembacaan profil/media untuk akun `artabumisentraindustri` melalui proses otorisasi Meta.
2. Simpan access token hanya di `.env`/secret server sebagai `INSTAGRAM_ACCESS_TOKEN`. Sesuaikan `INSTAGRAM_API_VERSION` dengan versi Graph API yang diaktifkan pada aplikasi (default integrasi `v24.0`). Token tidak ditampilkan dalam form, HTML, atau pesan kesalahan.
3. Jalankan `php artisan config:clear`, kemudian `php artisan instagram:sync`, atau tombol Sinkronkan sekarang di backend. Pastikan akun yang terhubung sesuai; integrasi menolak username lain.
4. Aktifkan Laravel scheduler di hosting: jalankan `php artisan schedule:run` setiap menit. Untuk pengembangan lokal, `php artisan schedule:work`. Jadwal sinkronisasi setiap 30 menit telah didaftarkan, tetapi proses scheduler belum dijalankan otomatis di komputer ini.
5. Pantau status dan waktu sinkronisasi pada backend. Kelola pembaruan token sesuai masa berlaku dari Meta; implementasi ini belum melakukan OAuth atau refresh token otomatis.

API: GET `https://graph.instagram.com/{version}/me` untuk memeriksa akun dan GET `/me/media` untuk mengambil media. Header bearer menyimpan token di server. Postingan foto/carousel menggunakan media URL; video menggunakan thumbnail. Caption mengikuti bahasa asli Instagram. Hanya tiga item terbaru yang disimpan. Pengunjung tidak memicu panggilan API. Saat API gagal, data terakhir dipertahankan. Tautan gambar Instagram dapat kedaluwarsa sehingga scheduler perlu aktif. Integrasi telah diuji dengan respons API simulasi; verifikasi langsung menunggu token akun.

Unggahan berita disimpan di `storage/app/public/news`; pastikan `php artisan storage:link` dijalankan di hosting. Jalankan migrasi sebelum mengakses halaman baru.
