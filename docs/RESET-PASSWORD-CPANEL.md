# Mengaktifkan reset password website

Reset akun website menggunakan token Laravel Fortify yang kedaluwarsa dan sekali pakai. Akun harus sudah ada di tabel `users`. Fitur ini tidak mengubah password Webmail dan tidak membuat akun baru.

## Deploy

Upload/pull perubahan `app/Http/Requests/CompanyPasswordResetLinkRequest.php`, `app/Providers/FortifyServiceProvider.php`, dan kedua view auth `forgot-password.blade.php` serta `login.blade.php`. Tidak perlu migration baru: tabel `password_reset_tokens` sudah termasuk migration awal proyek.

## SMTP cPanel

Di cPanel > Email Accounts > akun pengirim > Connect Devices, baca bagian **Secure SSL/TLS Settings**. Gunakan outgoing server dan port yang tercantum di sana. Jangan menebak hostname atau menonaktifkan verifikasi sertifikat.

Edit `.env` di `/home/artabumi/repositories/website-asi/.env`. Contoh berikut menggunakan SMTP SSL/TLS port 465; ganti placeholder dengan data nyata dari cPanel. Alamat pengirim harus mailbox yang benar-benar ada dan diizinkan mengirim.

```dotenv
APP_URL=https://artabumisentraindustri.com
APP_DEBUG=false
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=mail.artabumisentraindustri.com
MAIL_PORT=465
MAIL_USERNAME=it@artabumisentraindustri.com
MAIL_PASSWORD="PASSWORD_MAILBOX_PENGIRIM"
MAIL_FROM_ADDRESS=it@artabumisentraindustri.com
MAIL_FROM_NAME="Artabumi Sentra Industri"
```

Jika penyedia menetapkan port 587 dengan STARTTLS, gunakan `MAIL_SCHEME=smtp` dan `MAIL_PORT=587`. Jangan gunakan `MAIL_MAILER=log` untuk pengiriman inbox. Jika `MAIL_URL` sudah ada, perbarui atau hapus pengaturan lama itu karena dapat menimpa rincian SMTP. Jangan commit `.env` atau membagikan password dan tautan reset.

Setelah mengubah konfigurasi, jalankan `php artisan optimize:clear` dengan PHP hosting yang sesuai. Tanpa Terminal, buat cron sementara sekali per menit:

```sh
cd /home/artabumi/repositories/website-asi && /opt/alt/php83/usr/bin/php artisan optimize:clear >> /home/artabumi/reset-config-clear.log 2>&1
```

Periksa log melalui File Manager, lalu hapus cron sementara setelah berhasil. Pengiriman reset berjalan langsung melalui request web, tidak membutuhkan queue worker. Jangan mengubah APP_KEY.

## Verifikasi

1. Buka `https://artabumisentraindustri.com/forgot-password`.
2. Isi `it@artabumisentraindustri.com` dan kirim sekali.
3. Buka inbox/spam Webmail akun IT. Klik tautan dan isi password baru beserta konfirmasinya.
4. Pastikan login website dengan password baru berhasil, password lama tidak berlaku, dan tautan yang sama tidak dapat digunakan lagi.
5. Jika gagal kirim, periksa entri terbaru `storage/logs/laravel.log`. Periksa hostname, port, login SMTP, sertifikat, dan Email Deliverability cPanel (SPF/DKIM). Jangan mengunggah log berisi token ke tempat publik.

Uji otomatis lokal memakai notification fake, sehingga tidak membuktikan SMTP hosting dapat mengirim. Uji inbox di atas tetap diperlukan. Referensi: https://laravel.com/docs/13.x/passwords dan https://laravel.com/docs/13.x/mail.
