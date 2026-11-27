# KazuhaDev.

Dashboard lisensi multi-game Laravel 13 + MySQL, portal member, unduhan privat dan API validasi HWID.
Dibuat dan diverifikasi di lokal. Injector belum dimodifikasi dan website belum dipublikasikan.

## Jalankan di komputer ini

```powershell
cd D:\tester\keyforge
.\Start-Local.ps1
```

- Admin: http://127.0.0.1:8000/login
- Konfigurasi game dan upload injector/DLL: http://127.0.0.1:8000/games
- Daftar key: http://127.0.0.1:8000/licenses
- Member: http://127.0.0.1:8000/member/login
- API console: http://127.0.0.1:8000/api-console
- Pengaturan endpoint & WhatsApp: http://127.0.0.1:8000/settings
- MySQL lokal khusus project: 127.0.0.1:3307, database `keyforge`.
- Kredensial admin lokal tersimpan di `.local/access.txt`. File ini dan `.env` tidak dibagikan melalui Git.

Aset telah dibuild. Setelah perubahan CSS/JS: `npm run build`.
Untuk hot reload: `npm run dev` pada terminal lain.
Alternatif server: `php artisan serve --host=127.0.0.1 --port=8000`.
Pada komputer ini gunakan Start-Local.ps1 agar sys_temp_dir dan upload_tmp_dir memakai folder .local/php-tmp yang valid.

## Perilaku lisensi

1. Admin membuat key dengan akses satu atau lebih game dan durasi 1–30 hari, satu hari tepat 86.400 detik.
2. Saat dibuat: `activated_at = null`, `expires_at = null`, belum terikat HWID.
3. Login member melalui browser tidak mengaktifkan key.
4. Injector membaca fingerprint HWID yang stabil dari perangkat, lalu mengirim POST key + HWID + game_code. Kode game berasal dari injector tersebut.
5. Request pertama yang valid menyimpan HWID, activated_at, bound_at dan expires_at = activated_at + duration_seconds, dalam transaksi dengan row lock.
6. Request berikutnya hanya lolos jika kode game terdaftar, key memiliki akses ke game itu, HWID sama dan key belum expired/dicabut/dihapus.
7. Reset HWID tidak mengulang durasi atau mengubah aktivasi pertama. Matikan injector lama sebelum reset; perangkat pertama yang mengirim POST setelah reset akan terikat.
8. Perpanjangan admin: key aktif ditambah ke expired lama; key expired dimulai dari waktu perpanjangan; key belum aktif ditambah alokasinya tanpa memulai durasi.
9. Key yang dihapus menggunakan soft delete. API dan portal menolaknya, riwayat audit tetap tersedia.
10. Nama/catatan dapat diedit admin. Durasi awal dapat diganti sebelum aktivasi; setelah aktivasi gunakan Perpanjang.

## Portal member

Login dengan key saja. Member dapat melihat hitung mundur detik, HWID, waktu aktivasi/expired, menyalin key dan reset HWID miliknya. Game dan tautan unduhan injector hanya ditampilkan sesuai akses key; DLL hanya tersedia melalui API khusus injector. Unduhan diperiksa ulang oleh server pada tiap request.
Sinkronisasi ke server tiap 10 detik dan saat kembali ke tab.
Reset dibatasi sekali per menit.
Member tidak memiliki akses create/edit/delete/perpanjangan atau konfigurasi admin.
Key expired tetap dapat masuk untuk melihat status dan menghubungi admin, tetapi reset tidak tersedia.
Key dicabut/dihapus tidak dapat masuk; session lama juga ditolak.

Tombol perpanjangan mengarah ke WA dari Pengaturan API, format nomor kode negara tanpa tanda +.
Jika nomor belum diisi, tombol dinonaktifkan. Tautan tidak memuat key lengkap.
Tidak ada pesan WA atau pembayaran yang dikirim otomatis.

## API v1

### Aktivasi dan heartbeat

`POST /api/v1/licenses/validate`

Headers: `Accept: application/json`, `Content-Type: application/json`.

```json
{"key":"KF-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX","hwid":"HWID-PERANGKAT-YANG-STABIL","game_code":"rules-of-survival"}
```

Respons sukses: HTTP 200, success=true, code=valid, dengan data activated_at, expires_at, remaining_seconds, server_time dan heartbeat_interval.

### Status

`GET /api/v1/licenses/status`

Headers:
- `Accept: application/json`
- `Authorization: Bearer KEY_ANDA`
- `X-HWID: HWID_ANDA`
- `X-Game: rules-of-survival`

GET tidak mengikat HWID, mengaktifkan key, atau memperbarui last_seen.
Key/HWID tidak diterima melalui query URL.

| HTTP | code | Arti |
| --- | --- | --- |
| 200 | valid | Akses lolos |
| 401 | invalid_key | Tidak ditemukan, termasuk key yang dihapus |
| 403 | game_not_allowed | Game tidak termasuk akses key |
| 403 | game_unavailable | Kode game tidak dikenal |
| 403 | hwid_mismatch | HWID berbeda |
| 403 | expired | Masa berlaku berakhir |
| 403 | revoked | Dicabut admin |
| 409 | activation_required | GET sebelum binding POST |
| 422 | errors | Input hilang/format salah |
| 429 | rate limit | Terlalu banyak request |

Batas normal: 120 request/menit/IP dan 30/menit/key. Interval heartbeat bisa diatur 30–300 detik.
HWID case-sensitive, 8–255 karakter dari A–Z, a–z, 0–9, titik, garis bawah, titik dua, tanda hubung.

**Client harus fail closed:** izinkan hanya HTTP 200 + success=true + code=valid. Timeout, gagal TLS, JSON rusak, key tidak cocok atau server tidak tersedia harus menolak akses.
Jangan menggunakan jam lokal client sebagai otoritas expiry.
Validasi sertifikat HTTPS tetap aktif pada injector. Server mencocokkan HWID yang dikirim; keaslian fingerprint hardware harus dipertahankan di client.
API playground memakai HWID manual dan dapat mengaktifkan key sungguhan: gunakan key uji.

## Instalasi di server/STB nanti

Gunakan Linux yang mendukung PHP 8.5 dan ekstensi PDO MySQL, mbstring, XML, curl, intl, zip, bcmath, openssl, serta MySQL 8.
Lockfile dibuat dengan PHP 8.5. Jalankan composer check-platform-reqs pada target.
Build frontend dengan Node.js versi yang memenuhi package-lock; Node 24+ disarankan. Node tidak dibutuhkan saat melayani frontend yang sudah dibuild.

```sh
composer install --no-dev --optimize-autoloader
cp .env.example .env
# Isi DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD dan APP_URL.
php artisan key:generate
php artisan migrate --force
npm ci
npm run build
php artisan admin:create admin@domainanda.com
php artisan optimize
```

Buat database MySQL dan user khusus untuk database tersebut terlebih dahulu.
Gunakan APP_ENV=production, APP_DEBUG=false, SESSION_SECURE_COOKIE=true dan SESSION_ENCRYPT=true.
Document root Nginx/Apache HARUS direktori `public/`, bukan root project.
Berikan izin tulis terbatas pada storage/ dan bootstrap/cache. Jangan jalankan server development sebagai server publik.
Pasang HTTPS melalui reverse proxy yang benar dan gunakan domain itu pada APP_URL serta Pengaturan API.
Jangan expose port MySQL ke publik. Backup database bersama APP_KEY secara aman.
APP_KEY dibutuhkan untuk mendekripsi key dan HWID. Saat memindahkan data yang sudah ada, pertahankan APP_KEY; jangan menjalankan key:generate pada database lama.
File .local berisi data khusus Windows lokal dan tidak disalin ke server.
Deployment aktual belum dilakukan.

## Pengujian

```sh
php vendor/bin/phpunit
php vendor/bin/pint --format agent
npm run build
```

Default test memakai SQLite in-memory, tidak menyentuh database aplikasi.
Untuk regresi MySQL, pakai `Test-MySQL.ps1` yang mengarahkan test hanya ke database `keyforge_test`.
**Jangan menjalankan RefreshDatabase pada database produksi.**

## Struktur penting

- app/Services/LicenseService.php — aktivasi, binding, reset, edit, extend, revoke, delete.
- app/Http/Controllers/Api/LicenseController.php — endpoint injector.
- app/Http/Controllers/MemberController.php — portal member dan status realtime.
- app/Http/Middleware/AuthenticateMember.php — isolasi session member.
- routes/web.php dan routes/api.php — route aplikasi.
- tests/Feature/ — pengujian perilaku dan otorisasi.
- WORKLOG.md — seluruh arahan, perubahan, hasil verifikasi dan rencana berikutnya.

## Berikutnya

Integrasi HWID dan heartbeat pada injector, lalu deployment ke server sendiri.
Payment gateway belum dipasang. Alur extend berada di service agar nanti dapat dipanggil oleh webhook pembayaran yang memverifikasi signature dan idempotensi, bukan langsung oleh member/client.

## Akun dan role admin

Menu **Kelola Admin** di /admins dapat menambah admin melalui form nama/email/password.
Server menetapkan role admin; tidak ada pilihan mencampur akun admin dan key member.
Default user baru adalah member dan role tidak dapat diisi melalui mass assignment.
Semua route admin dilindungi autentikasi email/password dan pemeriksaan role admin.
Portal member tetap menggunakan license key dengan session terpisah secara identitas.
Password admin baru minimal 12 karakter, huruf besar/kecil dan angka.
Pembuatan admin tercatat dalam activity log. Kredensial utama lokal ada di .local/access.txt.

Hasil verifikasi terakhir: 95 tests / 636 assertions lolos pada SQLite dan MySQL.


## Identitas game dan rilis privat
- Admin menambah game pada menu Konfigurasi Game. Kode unik, contoh rules-of-survival, bersifat permanen agar konfigurasi injector tidak berubah saat nama game diedit.
- Tidak ada status aktif/nonaktif game sebagai syarat validasi. Identitas game diambil dari game_code / X-Game yang dikirim injector.
- Satu key dapat mengakses beberapa game, tetapi memakai satu HWID dan satu masa berlaku bersama. Mengganti game tidak memulai ulang durasi.
- Server memeriksa kode game dan akses key sebelum menyimpan HWID. Kode game adalah klaim dari client; server tidak menginspeksi executable atau membuktikan game dari proses yang berjalan.
- Admin bisa upload injector (.exe / .zip) dan DLL (.dll) secara terpisah, masing-masing maksimal 20 MB. Disimpan di storage/app/private/game-builds dengan nama acak; SHA-256 dan ukuran tersimpan.
- Saat upload baru, berkas lama diganti setelah transaksi database berhasil. Admin bisa menghapus berkas injector/DLL secara terpisah melalui edit konfigurasi.
- Tidak ada symbolic link publik atau URL mentah untuk berkas. Admin mengunduh melalui route admin; member hanya melalui /member/games/{game}/downloads/injector. DLL lewat API tervalidasi.
- Download member memerlukan login key, akses game yang sesuai, dan key belum expired/dicabut/dihapus. Key pending diperbolehkan mengunduh agar dapat melakukan aktivasi pertama.
- Mengganti URL ke game lain tetap ditolak server. Setiap unduhan sukses dan perubahan rilis dicatat pada Activity log.
- Belum ada injector/DLL asli yang diunggah; tombol unduh muncul setelah admin mengunggah berkas.
- Saat deploy, sesuaikan upload_max_filesize minimal 20M, post_max_size lebih dari 40M dan batas body reverse proxy; pastikan direktori upload sementara dapat ditulis. Jangan jalankan berkas unggahan di server.

## Tema dan domain
KazuhaDev. menggunakan tema pink/lavender dan font Plus Jakarta Sans dengan dark/light mode pada admin, member dan login. Tema disimpan per browser; animasi mengikuti prefers-reduced-motion.
Domain tujuan nanti: https://ros.kazuhadev.com. Dokumentasi API menggunakan domain tersebut; APP_URL lokal tetap http://127.0.0.1:8000, dan playground menggunakan origin yang sedang dibuka.


## Pembaruan UX dan identitas member
- Semua konfirmasi/reset/revoke/delete/playground memakai dialog custom sesuai tema, bukan window.confirm. Cancel, Escape dan backdrop tidak mengeksekusi tindakan.
- Toast universal sukses/error/info pada admin/member/login/API; error persisten, bisa ditutup. Clipboard memiliki fallback manual. Form tetap menggunakan validasi browser/server dan CSRF.
- Nama member terpisah dari nama key (label). Data lama menggunakan label sebagai nama awal; admin dapat mengeditnya. Nama member dan license key berada di atas informasi durasi.
- Kartu game maksimal 3 kolom, 1 kartu center, turun menjadi 2/1 kolom. Banner/logo dapat diunggah, diedit atau dihapus di Konfigurasi Game; preview langsung. Default ROS memakai header/logo dari Steam.
- Banner: JPG/PNG/WebP maksimal 5 MB; logo maksimal 2 MB. SVG dan skrip tidak diterima sebagai gambar.
- Activity log dibatasi 8 item/halaman dalam area scroll maksimal 410px.

## API unduhan DLL
GET /api/v1/games/dll dengan Authorization: Bearer KEY, X-HWID dan X-Game.
Wajib aktivasi pertama lebih dahulu. DLL ditolak bila key/HWID/game tidak sesuai, key expired/revoked/deleted atau rilis belum tersedia. Endpoint tidak mengikat HWID baru atau memulai durasi. Sukses menghasilkan attachment binary, X-Checksum-SHA256 dan Cache-Control no-store. Error menghasilkan JSON non-200. Injector harus memeriksa status, jenis respons dan checksum sebelum menggunakan hasil unduhan.
Unduhan langsung dari website memakai session hasil login key dan diperiksa ulang tiap request; tidak ada URL publik ke binary atau key di query string.

## Katalog, pesanan dan keuangan
- Admin: /orders untuk membuat/mengedit/menyembunyikan paket dan memverifikasi pembayaran pesanan. Paket memiliki harga IDR, durasi hari, deskripsi dan akses game.
- Member: /member/shop untuk memilih paket, membuat pesanan, membatalkan pesanan sendiri yang pending dan melihat riwayat/status/key baru.
- Harga, durasi dan akses game disalin ke order saat pemesanan. Client tidak menentukan harga. Token idempotensi mencegah pesanan dobel dari submit ulang.
- Pembayaran saat ini manual. Admin memasukkan referensi unik setelah dana benar-benar diterima. Transaksi DB menyelesaikan order, menerbitkan satu key baru dan mencatat satu ledger income; konfirmasi berulang tidak menggandakan key atau uang.
- Key pembelian belum aktif sampai aktivasi pertama pada injector. Member dapat menyalin key hasil pembelian dari riwayat pesanan lalu login dengan key baru.
- /finance: laporan berdasarkan waktu pembayaran WIB, filter maksimal 366 hari, pemasukan bruto, biaya, pengeluaran dan arus kas bersih = bruto − biaya − pengeluaran.
- Hanya ledger posted dihitung. Pending/cancelled order tidak dihitung; void manual diaudit dan dikeluarkan dari total tanpa hard delete. Transaksi order tidak di-void dari ledger umum.
- Grafik dashboard, tabel dan export .xlsx memakai FinanceReportService yang sama. Workbook memuat Ringkasan dan Transaksi, nilai IDR numerik, serta teks inline yang tidak dieksekusi sebagai formula.
- Database aplikasi tidak diisi contoh pemasukan untuk demo. Harga paket ditentukan admin.
- Payment gateway belum dipilih/dikonfigurasi. Tidak ada webhook atau konfirmasi pembayaran dari client. Integrasi nanti wajib memverifikasi signature, order/reference, amount, currency, status settlement/fees dan idempotensi sebelum memanggil proses pemenuhan order. Refund belum tersedia.

## Notifikasi dan pembaruan otomatis
- Bell notifikasi admin/member dengan unread per scope/browser, mark read, daftar terbaru dan toast aktivitas baru.
- Polling setiap 10 detik serta ketika tab kembali aktif. Feed member hanya aktivitas key sendiri; feed admin tetap memerlukan role admin.
- Konten yang berubah disegarkan otomatis. Dialog terbuka, form yang sedang diedit, input berfokus dan teks terseleksi menunda penggantian konten agar pekerjaan pengguna tidak tertimpa.
- Countdown bergerak setiap detik, kemudian disinkronkan ke waktu server.
