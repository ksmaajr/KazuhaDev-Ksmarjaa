# Kontrak API aplikasi dan DLL KazuhaDev

Dokumen ini digunakan pengembang aplikasi desktop dan DLL milik pengelola game. Server memilih rilis menurut game dan versi yang diminta, serta memverifikasi lisensi sebelum memberikan data akun atau berkas privat.

## Status implementasi

Server Laravel mendukung beberapa DLL per game dan data akun pada API validasi/status. Implementasi deteksi launcher, autentikasi di dalam DLL, penyimpanan key desktop, heartbeat setelah jendela injector ditutup, dan pembersihan file merupakan pekerjaan client terpisah. Perubahan server ini tidak mengubah binary injector/DLL dan tidak membuktikan server publik sudah diperbarui.

File hasil aplikasi desktop yang diminta pengguna bernama `KZ Injector.exe`, dengan merek `ROS KazuhaDev`. Source draft desktop masih berada di workspace induk, bukan repository Laravel ini.

## Identitas game dan versi

`game_code` / `X-Game` menunjukkan produk game yang diizinkan oleh lisensi, misalnya `rules-of-survival`. `X-Game-Version` memilih varian rilis dari produk yang sama, misalnya `infinity` atau `mirae`. Versi tidak membuat lisensi baru, tidak menambah akses game, dan tidak mengulang masa berlaku.

Admin membuka Konfigurasi Game, memilih Edit, lalu Tambah versi game. Setiap baris memiliki nama tampilan, kode versi permanen, dan berkas DLL. Contoh:

| Nama versi game | Kode versi API | DLL |
| --- | --- | --- |
| ROS Infinity | infinity | unggahan DLL Infinity |
| ROS Mirae | mirae | unggahan DLL Mirae |

Nama game utama tetap satu, misalnya Rules Of Survival. Nama versi dapat diganti tanpa mengubah kode API. Kode memakai huruf kecil ASCII, angka, dan tanda hubung di antara bagian; maksimal 50 karakter. Maksimal 20 versi per game, nama maksimal 100 karakter, dan setiap berkas maksimal 20 MB. Versi baru wajib berkas; versi lama dapat diganti nama/berkas atau dihapus melalui checkbox saat menyimpan. Versi lain yang tidak disertakan dalam request edit dipertahankan. Semua berkas tetap privat dan tidak dijalankan server.

Form admin hanya menyediakan unggahan DLL per versi. Data DLL lama dipertahankan di database untuk kompatibilitas migrasi, tetapi kolom unggah dan baris DLL tanpa versi tidak ditampilkan lagi. Request tanpa X-Game-Version hanya berlaku bila game belum mempunyai varian. Begitu minimal satu varian dikonfigurasi, header versi wajib; server tidak memilih otomatis atau beralih ke DLL lama. Request dengan versi tidak dikenal selalu ditolak, termasuk saat DLL lama tersedia.

Deteksi launcher merupakan tanggung jawab aplikasi desktop: periksa executable/proses/path instalasi yang benar, cocokkan ke kode versi admin, lalu kirim header. Jika deteksi ambigu atau versi tidak dikenal, hentikan dan tampilkan status yang jelas. Jangan menebak Infinity sebagai default. Header versi merupakan klaim client, bukan bukti kriptografis launcher yang dijalankan.

## Autentikasi awal

Gunakan HTTPS. Base URL contoh: `https://ros.kazuhadev.com/api/v1`. Untuk localhost pengembangan gunakan origin lingkungan uji yang terpisah; jangan menonaktifkan verifikasi sertifikat pada build distribusi.

```http
POST /api/v1/licenses/validate
Accept: application/json
Content-Type: application/json

{"key":"KEY_DARI_PENGGUNA","hwid":"FINGERPRINT_PERANGKAT","game_code":"rules-of-survival"}
```

POST pertama yang sah mengikat HWID dan memulai durasi. Login portal website atau GET status tidak memulai durasi. Satu lisensi memiliki satu HWID dan masa berlaku bersama untuk seluruh akses game. HWID harus stabil, case-sensitive, 8–255 karakter dari huruf ASCII, angka, titik, garis bawah, titik dua, dan tanda hubung. Client yang sama harus memakai algoritma fingerprint yang sama; DLL tidak boleh membuat fingerprint berbeda dari aplikasi desktop.

## Data akun untuk DLL

DLL dapat meminta data akun langsung melalui endpoint status setelah aktivasi berhasil:

```http
GET /api/v1/licenses/status
Accept: application/json
Authorization: Bearer KEY_DARI_PENGGUNA
X-HWID: FINGERPRINT_PERANGKAT
X-Game: rules-of-survival
```

GET tidak mengaktifkan, mengikat HWID, atau memperbarui last_seen. Respons sukses POST dan GET memuat kontrak lama serta objek tambahan `data.account`:

```json
{
  "success": true,
  "code": "valid",
  "data": {
    "account": {
      "member_name": "Nama member",
      "key_hint": "KEY_TERSAMARKAN",
      "package": {
        "name": "Nama paket saat dipesan",
        "duration_seconds": 604800
      },
      "games": [{"code": "rules-of-survival", "name": "Rules Of Survival"}]
    },
    "game": {"code": "rules-of-survival", "name": "Rules Of Survival"},
    "status": "active",
    "hwid_bound": true,
    "expires_at": "2026-12-04T07:00:00+00:00",
    "remaining_seconds": 604800,
    "server_time": "2026-11-27T07:00:00+00:00",
    "heartbeat_interval": 60
  }
}
```

Contoh di atas memperlihatkan bagian respons yang relevan; respons sebenarnya juga memuat license_id, label, newly_bound, created_at, dan activated_at. Nilai contoh bukan kredensial atau akun sungguhan.

`account.member_name` adalah nama member, bukan email admin. `key_hint` dipakai untuk tampilan key; server tidak mengembalikan key lengkap yang sudah dimiliki client untuk autentikasi. Jangan menaruh key lengkap dalam log, URL, screenshot diagnostik, atau file plaintext.

`account.package` memakai snapshot nama/durasi dari pesanan lunas terakhir yang menerbitkan atau memperpanjang lisensi ini, diurutkan berdasarkan paid_at lalu ID. Pesanan pending dan sandbox diabaikan. Key manual tanpa pesanan mengembalikan `null`; jangan mengarang nama paket dari label key. Durasi paket adalah durasi pesanan terakhir, bukan sisa masa berlaku atau akumulasi seluruh perpanjangan. Untuk masa berlaku aktual gunakan expires_at dan remaining_seconds.

`account.games` memuat seluruh akses game lisensi. `data.game` menunjukkan game yang sedang divalidasi. Paket tidak menjadi pengganti otorisasi server pada tiap request. Akun expired/revoked/HWID salah tidak mendapat data akun; respons ditolak dengan data null.

## Unduh DLL menurut versi

```http
GET /api/v1/games/dll
Accept: application/octet-stream
Authorization: Bearer KEY_DARI_PENGGUNA
X-HWID: FINGERPRINT_PERANGKAT
X-Game: rules-of-survival
X-Game-Version: infinity
```

Untuk Mirae ganti nilai header menjadi `mirae`. Server memeriksa key aktif, binding HWID, dan akses game sebelum memilih rilis. GET tidak mengaktifkan lisensi atau mengikat ulang HWID.

Sukses HTTP 200 mengembalikan binary `application/octet-stream`, nama berkas berdasarkan game/versi, header `X-Checksum-SHA256`, `X-Game-Version`, `X-Content-Type-Options: nosniff`, serta `Cache-Control: private, no-store`. Lokasi penyimpanan server tidak disertakan dalam respons. Audit menyimpan game, versi, checksum, dan lisensi pemohon.

Client wajib memeriksa HTTP status, jenis respons, versi yang dikembalikan, ukuran berkas, SHA256 hasil unduhan, dan format/arsitektur berkas sebelum memakainya. JSON error atau halaman HTML tidak boleh disimpan sebagai DLL. Jika unduhan gagal, hapus berkas parsial. Jangan mengikuti redirect lintas host sambil membawa Authorization. Checksum dari server lewat HTTPS melindungi integritas transfer; itu bukan pengganti tanda tangan publisher.

## Error dan pembatasan

| HTTP | Kode | Tindakan client |
| --- | --- | --- |
| 401 | invalid_key | Tolak akses; minta key yang benar |
| 403 | hwid_mismatch | Tolak; arahkan reset HWID melalui website |
| 403 | expired | Tolak; tampilkan masa berlaku berakhir |
| 403 | revoked | Tolak; hubungi admin |
| 403 | game_not_allowed | Tolak game di luar akses |
| 403 | game_unavailable | Periksa konfigurasi kode game |
| 409 | activation_required | Aktivasi melalui POST dengan key/HWID yang sama |
| 422 | game_version_required | Kirim X-Game-Version; jangan memilih DLL acak |
| 422 | validation error | Perbaiki format input/header |
| 404 | release_unavailable | Rilis versi belum tersedia; jangan fallback versi lain |
| 429 | rate limited | Tunggu dan ulangi dengan jeda |

Hanya HTTP 200 + success true + code valid yang memberi izin autentikasi. Non-JSON, JSON rusak, timeout, TLS gagal, respons game berbeda atau status ditolak harus menutup jalur akses. Jangan membedakan sukses berdasarkan redirect browser atau teks pesan saja.

Endpoint lisensi dibatasi normalnya 120 request/menit/IP dan 30/menit/key; heartbeat server 30–300 detik. Gunakan server_time/remaining_seconds untuk sinkronisasi, bukan jam PC sebagai otoritas expiry. Countdown lokal hanya tampilan; verifikasi server menentukan akses.

## Aturan client yang perlu diimplementasikan

- Simpan key hanya atas pilihan pengguna, memakai penyimpanan kredensial Windows/DPAPI untuk user tersebut. Jangan menyimpan plaintext atau memakai key admin/gateway di binary.
- Key tersimpan tetap diperiksa ke server setiap aplikasi dibuka. Berikan opsi ganti/hapus key tersimpan.
- DLL dan aplikasi harus memiliki jalur pemindahan kredensial yang terotorisasi jika DLL melakukan request sendiri. Jangan memakai argument command line, environment global, atau file plaintext bersama. Desain IPC kredensial belum diimplementasikan oleh perubahan server ini.
- Jika aplikasi ditutup sesudah menu siap, tentukan komponen yang tetap melakukan heartbeat. Menutup aplikasi tidak otomatis memindahkan verifikasi ke DLL. GET status membaca akun; POST validate memperbarui last_seen. Gunakan interval server dan jangan menumpuk loop request.
- Rilis disimpan dalam direktori aplikasi user dengan hak akses terbatas, tanpa menampilkan path pada UI. Pemilik PC/administrator tetap dapat menemukan berkas; jangan menjanjikan lokasi tidak dapat diketahui.
- Auto-close menunggu konfirmasi render/menu yang benar dari sesi proses yang sama, bukan hanya keberhasilan memuat DLL. Jangan mengulang pemuatan pada PID/sesi yang sudah berhasil atau belum pasti.
- Cleanup hanya menghapus berkas unduhan sesi sendiri setelah proses game benar-benar berakhir, termasuk crash. Identitas proses harus mencakup creation time untuk mencegah salah PID yang dipakai ulang. Berkas terkunci perlu retry dan recovery startup; jangan menghapus berkas pihak lain.
- Tampilan desktop mengikuti merek ROS KazuhaDev: progress/status, key tersamarkan, durasi, dan akses game. Technical log lokal tidak ditampilkan pada UI dan tidak memuat kredensial/HWID mentah.

## Update server

Migrasi baru `2026_11_27_064953_add_dll_variants_to_games_table.php` menambah kolom JSON nullable pada games tanpa menghapus DLL lama. Jalankan backup database dan berkas privat sebelum update. Pertahankan APP_KEY dan .env server.

Setelah checkout commit update yang sudah disepakati:

```sh
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force --no-interaction
npm ci
npm run build
php artisan optimize --no-interaction
```

Build frontend dapat dilakukan di CI dan aset dikirim melalui proses deploy yang berlaku. Repo tidak menyimpan public/build, vendor, node_modules, database, secret, atau unggahan privat. Sesuaikan post_max_size/max_file_uploads/batas body web server dengan total unggahan: 20 DLL sekaligus bisa melebihi 400 MB. Mengunggah beberapa versi bertahap mengurangi kebutuhan ukuran satu request. Server dan proxy harus menolak request terlalu besar secara jelas.

Uji dengan key QA: upload Infinity/Mirae, aktivasi, request kedua versi, cocokkan checksum dan header versi, lalu uji wrong-HWID/expired/wrong-game/unknown-version dan data akun. Jangan menjalankan RefreshDatabase pada database server produksi. Jalankan `php vendor/bin/phpunit` hanya di lingkungan pengujian terisolasi.

Repository deployment: https://github.com/ksmaajr/KazuhaDev-Ksmarjaa, branch main. Commit cdd8da0 memuat source baseline dari ZIP awal; commit sesudahnya memuat update DLL per versi dan data akun. Agent server yang sudah memakai checkout repo ini dapat menjalankan git fetch origin lalu git pull --ff-only origin main, memeriksa diff, kemudian menjalankan migrasi dan build di atas. Jika server bermula dari ZIP tanpa Git, cocokkan baseline terlebih dahulu; clone ke direktori rilis terpisah, pertahankan .env/APP_KEY serta storage privat, dan gunakan database yang sama. Jangan menimpa direktori live atau membuang modifikasi agent deploy. Untuk menarik hanya update setelah baseline, gunakan git diff cdd8da0..origin/main; tidak perlu memasang ulang database atau mengunggah ulang DLL lama.
