# Instagram Unified Login di cPanel

Integrasi mendukung dua jalur eksplisit. Default `instagram` memakai
`graph.instagram.com/{version}/me`. Mode `facebook` memakai
`graph.facebook.com/{version}/{account_id}` untuk token yang sudah diuji
melalui Graph API Explorer. Tidak ada deteksi token atau fallback otomatis.
Host API dibatasi ke dua host Meta tersebut.

## Konfigurasi production

Tarik kode terbaru melalui cPanel Git Version Control > Manage > Update from Remote.
Edit `/home/artabumi/repositories/website-asi/.env`:

```ini
INSTAGRAM_LOGIN_TYPE=facebook
INSTAGRAM_ACCOUNT_ID=17841471039835117
INSTAGRAM_API_VERSION=v26.0
```

Pertahankan `INSTAGRAM_ACCESS_TOKEN` yang berhasil mengakses profil dan media di
Graph API Explorer. Jangan masukkan token ke Git atau screenshot.
Versi v26.0 dan ID di atas mengikuti hasil pengujian akun perusahaan.

Bersihkan cache konfigurasi. Jika tidak ada terminal, buat cron sementara setiap
menit dengan perintah:

```sh
/opt/alt/php83/usr/bin/php /home/artabumi/repositories/website-asi/artisan config:clear >> /home/artabumi/instagram-config-clear.log 2>&1
```

Setelah log menunjukkan sukses, hapus cron sementara saja. Pertahankan cron
`instagram:sync`. Klik Sinkronkan sekarang pada halaman HR dan pastikan waktu
sinkronisasi terakhir berubah serta gambar tampil.

Token yang berhasil membaca profil belum tentu memiliki akses media; uji kedua
endpoint sebelum mengganti konfigurasi. Website tetap memeriksa username akun
perusahaan dan mempertahankan data lama jika permintaan gagal.

Untuk kembali ke token Instagram Login, set `INSTAGRAM_LOGIN_TYPE=instagram`,
gunakan token dan versi yang sesuai, lalu bersihkan cache konfigurasi.
