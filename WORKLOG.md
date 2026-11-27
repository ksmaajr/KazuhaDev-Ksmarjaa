# Keyforge — Catatan pengerjaan

Tanggal mulai: 27 November 2026 (WIB).
Lokasi: `D:\tester\keyforge`.
Status: selesai untuk website lokal dan API; server lokal tetap berjalan. Tidak dipublikasikan.

## Arahan yang menjadi spesifikasi

1. Laravel stabil terbaru, database MySQL, dashboard premium, login admin, API POST dan GET untuk integrasi injector.
2. Website diselesaikan dan berjalan di lokal terlebih dahulu. Injector lama tidak diubah. Hosting nantinya di STB/server pribadi.
3. Revisi: durasi dihitung sejak aktivasi pertama melalui injector + HWID, bukan sejak key dibuat.
4. Durasi cukup dipilih 1–30 hari; satu hari tepat 24 jam / 86.400 detik. Tidak perlu mengisi jam, menit, atau detik.
5. Satu key satu HWID; auto-bind pertama, tolak HWID berbeda, reset tanpa mereset durasi.
6. Admin dapat membuat, menyalin, mengedit, memperpanjang, reset HWID, mencabut, dan menghapus key.
7. Member masuk dengan key saja. Portal tidak memulai durasi.
8. Member hanya melihat durasi realtime dan HWID, menyalin key miliknya, reset HWID sendiri, dan menghubungi WA admin untuk perpanjangan.
9. Perpanjangan manual hanya oleh admin. Payment gateway masih rencana berikutnya.
10. Nomor WhatsApp admin dan alamat endpoint dapat diatur melalui dashboard.

## Implementasi

- Scaffold Laravel 13, dependency terkunci di composer.lock dan package-lock.json.
- Framework terpasang: Laravel 13.44.1; PHP lokal 8.5.11.
- Dashboard Blade + Vite, tema gelap mint, navigasi mobile, grafik data riil dan activity log.
- Login admin session, CSRF, pembatasan percobaan login; akun dibuat melalui CLI, tanpa registrasi publik.
- MySQL 8.0.46 khusus project di loopback port 3307. Database keyforge dan keyforge_test terpisah.
- Kredensial lokal berada di .env / .local (diabaikan Git), tidak dicatat di sini.
- Key random 128-bit, hash SHA-256 untuk lookup, nilai key terenkripsi agar dapat disalin kembali; HWID terenkripsi.
- Transaksi dan row lock untuk aktivasi, reset, edit, perpanjangan, pencabutan dan penghapusan.
- expires_at kosong sampai aktivasi pertama; activated_at dicatat sekali.
- Reset HWID mempertahankan activated_at dan expires_at.
- Penghapusan memakai soft delete: hilang dari dashboard/member/API, audit tidak hilang.
- Perpanjangan key aktif menambah expired; key expired mulai dari sekarang; key belum aktif menambah alokasi tanpa aktivasi.
- Member terikat ke license ID dalam session, bukan ID yang dikirim form. Akses diverifikasi ulang tiap request.
- Tautan WA memuat ID/label lisensi, tidak memuat key lengkap. Tidak ada pesan yang dikirim otomatis.
- Pengaturan origin server, interval heartbeat, dan nomor WA admin.
- API console berisi kontrak respons, contoh cURL dan playground request sungguhan.

## Riwayat verifikasi awal

- Build Vite dan compile Blade berhasil sebelum penambahan member.
- 31 tes / 194 assertions lolos pada database MySQL sebelum revisi aktivasi dan penambahan member.
- Browser: login admin, pembuatan key, aktivasi POST, GET status cocok, GET beda HWID ditolak, reset HWID, pengaturan interval berhasil.
- Desktop dan mobile diperiksa; verifikasi akhir dilakukan setelah semua perubahan terbaru.

## Tahap berikutnya

- Integrasi client injector dilakukan pada tahap berikutnya setelah website disetujui.
- Deployment STB/server memerlukan akses host/domain/HTTPS dan konfigurasi produksi.
- Payment gateway memerlukan pilihan provider, akun merchant, webhook signature, idempotensi pembayaran dan rekonsiliasi.

## Penyelesaian dan perubahan tambahan

- Ditambahkan menu Kelola Admin. Admin utama dapat membuat admin baru dengan nama, email, password dan konfirmasi.
- users.role memiliki default member; CLI dan form admin menetapkan admin secara eksplisit. Role tidak mass assignable.
- Login admin memeriksa role=admin. EnsureAdmin memeriksa ulang role pada seluruh route admin, termasuk API console dan Kelola Admin.
- Member tetap menggunakan key/session member_license_id, bukan autentikasi email/password admin. Member tidak mendapat hak admin.
- Admin listing memfilter role admin, sehingga akun member tidak tercampur.
- Pembuatan admin dicatat di activity log beserta actor, target user/email dan IP. Password di-hash dan tidak dicatat di log.
- Password admin baru minimal 12 karakter, huruf besar/kecil dan angka; duplikat email dan override role ditolak.
- Perbaikan clipboard: tombol Salin key diperjelas, nilai key terenkripsi dapat diambil ulang, fallback clipboard dan dialog salin manual tersedia.
- Key uji awal dari penyimpanan hash lama direkonsiliasi menggunakan plaintext yang sebelumnya diterbitkan; tidak ada upaya menebak key.
- Bukti browser clipboard: key disalin dari portal member, dipaste ke API playground, lalu POST mendapat HTTP 200 untuk license yang benar.
- Revisi durasi: form hanya hari; countdown tetap sampai detik dan disinkronkan ke server setiap 30 detik.
- Perbaikan multi-tab: redirect create/edit/extend/delete/reset/settings/login menggunakan route tujuan eksplisit agar request member tidak mengubah halaman kembali admin.
- Start-Local.ps1 menjalankan atau menggunakan ulang server lokal + MySQL. Sudah dijalankan dan endpoint login merespons.
- Test-MySQL.ps1 mengisolasi pengujian ke keyforge_test di 127.0.0.1:3307 dan mengembalikan environment sesudahnya.
- .env.example menggunakan MySQL. README mencakup setup, API, member, aturan durasi dan panduan awal deploy.
- Kredensial utama disimpan terpisah di .local/access.txt, tidak dimasukkan ke WORKLOG/README/source publik.

## Hasil akhir verifikasi

- PHPUnit default SQLite in-memory: 59 tests / 376 assertions, seluruhnya lolos.
- PHPUnit pada MySQL lokal keyforge_test: 59 tests / 376 assertions, seluruhnya lolos.
- Mencakup first activation setelah menunggu 40 hari, expiry di batas detik, mismatch HWID, binding ulang, reset, extension aktif/expired/pending, soft delete, member isolation, role admin/member, login throttling, konfigurasi WA dan redirect lintas tab.
- Vite production build berhasil; compile Blade berhasil; format PHP dirapikan dengan Pint.
- Composer check-platform-reqs --no-dev lolos pada PHP lokal.
- Browser desktop/mobile: login admin, create, edit, copy, POST activation, GET status/mismatch, reset admin/member, durasi realtime dan perpanjangan 24 jam terverifikasi.
- Uji delete browser menggunakan key sementara QA Disposable yang dibuat khusus, lalu soft delete; key hilang dari daftar. Data key buatan pengguna tidak dihapus.
- Nomor WhatsApp admin belum diisi karena belum diberikan; tombol WA menunggu konfigurasi di Pengaturan API.
- Batas verifikasi: injector belum dimodifikasi, HWID playground masih input uji; belum ada deploy publik, TLS produksi, stress test atau payment gateway.


## 27 November 2026 — KazuhaDev dan konfigurasi multi-game (dalam pengerjaan)
- Branding KazuhaDev., tema Sakura/Amethyst pink-lavender, animasi, dark/light mode untuk admin/member/login; preferensi tema tersimpan.
- Domain dokumentasi API: https://ros.kazuhadev.com; aplikasi tetap lokal di 127.0.0.1:8000.
- Menyiapkan tabel games dan relasi many-to-many dengan key. Migrasi memberi akses Rules Of Survival pada key lama.
- API POST wajib game_code dan GET wajib X-Game. Otorisasi game aktif dan akses key dilakukan sebelum binding HWID.
- Admin dapat memilih beberapa game saat create/edit key; konfigurasi game mencakup kode permanen, status, versi dan upload injector/DLL terpisah.
- Berkas privat, maksimal 20 MB per berkas; unduhan member dibatasi akses key dan status lisensi/game. Key pending boleh mengunduh tanpa aktivasi agar injector dapat dipasang.
- Audit untuk perubahan game dan unduhan; tidak ada berkas injector pengguna yang diambil otomatis.
- Permintaan perubahan email/password admin utama diterima; nilai password tidak dimasukkan catatan publik.

### Koreksi pengguna: identitas game berasal dari injector
- Tidak ada syarat/status game aktif terpisah. Injector mengirim game_code (POST) atau X-Game (GET); server mencari kode terdaftar dan memeriksa akses key.
- UI status game dihapus; migrasi tambahan menghapus kolom is_active. Pemeriksaan HWID dan expiry key tetap berlaku.
- Email/password admin utama berhasil diganti dan hash password diverifikasi. Password hanya di .local/access.txt yang diabaikan dari source publik.


## Verifikasi multi-game dan rilis privat — 27 November 2026
- SQLite in-memory: 75 tests / 474 assertions lolos.
- MySQL lokal khusus keyforge_test: 75 tests / 474 assertions lolos.
- Cakupan baru: banyak game per key, edit akses, game dari request injector, kode hilang/tidak dikenal/di luar akses ditolak tanpa binding HWID, satu HWID/expiry bersama antar-game.
- Cakupan unduhan: key pending boleh download tanpa aktivasi; anonim/expired/revoked/deleted/key tanpa akses ditolak; URL game lain ditolak; jenis berkas dan ukuran divalidasi; replacement/removal file dan audit diuji dengan fixture inert.
- Tidak ada binary injector/DLL pengguna yang diambil atau dijalankan.
- Login HTTP admin dengan email/password baru berhasil sampai dashboard; password tidak ditulis dalam catatan.
- PHP lokal sebelumnya memakai upload_tmp_dir dari drive F yang sudah tidak valid. Start-Local.ps1 kini menetapkan sys_temp_dir dan upload_tmp_dir ke .local/php-tmp, kemudian server lokal dijalankan ulang.
- Pengujian upload menemukan realpath C:/Windows/Temp tidak dapat digunakan di lingkungan ini. Tes sekarang dijalankan dengan .local/php-tmp; fixture upload hanya teks inert.
- Browser QA terpisah: Konfigurasi Game, form injector/DLL, pilihan game create key, portal member sesuai akses, dark/light toggle dan persistensi setelah reload terverifikasi. Tab dan key milik pengguna tidak diubah.
- Vite build dan compile Blade berhasil. Pint --dirty tidak berlaku karena folder bukan repository Git; Pint penuh berhasil.
- Website tetap lokal. Injector belum diubah dan deployment/publication belum dilakukan.


## 27 November 2026 — Upgrade UX, library dan pembelian (dalam pengerjaan)
- Arahan: seluruh konfirmasi/notifikasi custom seragam admin/member; kartu game maksimum 3 kolom, satu kartu center, banner/logo game dan gradasi termasuk game picker; gambar dapat diunggah admin.
- Member hanya mengunduh injector setelah login key; DLL melalui API Bearer key + HWID + game dengan pemeriksaan akses/expiry. File binari tetap privat.
- Nama member dipisahkan dari nama key, informasi key dipindahkan ke bagian paling atas setelah nama member.
- Font akan disesuaikan, animasi/responsif/interaksi mengikuti tema dark/light.
- Menambahkan ledger keuangan, grafik pemasukan, laporan/export Excel dan katalog/pesanan pembelian key yang terlacak.
- Payment gateway belum dipilih/dikonfigurasi: pembayaran awal manual diverifikasi admin. Tidak akan menampilkan pembayaran gateway fiktif. Pencatatan pendapatan, penerbitan key dan penyelesaian order harus atomik serta idempoten.


### Implementasi UX dan perdagangan
- Mengganti window.confirm (reset admin/member, revoke/delete, API playground) dengan dialog custom promise-based; Escape/tutup/backdrop membatalkan. Submit menjaga CSRF/method dan submitter, dengan pencegahan klik ganda.
- Notifikasi toast custom sukses/error/info, tombol tutup, progress waktu, pause hover/fokus; error tetap terlihat sampai ditutup. Validasi form memakai toast; fallback copy manual juga seragam.
- Tema dark/light tetap dipakai. Font Plus Jakarta Sans 400/500/600/700 dibundel lokal melalui Vite, tanpa ketergantungan CDN saat runtime.
- Kartu game maksimum 3 kolom; 1 center, 2 center, responsif 2/1 kolom. Banner/logo, overlay gradient dan hover; game picker memakai gambar yang sama.
- Admin bisa upload/edit/hapus banner (5MB) dan logo (2MB), JPG/PNG/WebP dengan validasi image/dimensi, preview dan cleanup replacement.
- Default Rules Of Survival memakai artwork/header Steam dan logo pada pengumuman Steam resmi. Sumber header: https://shared.fastly.steamstatic.com/store_item_assets/steam/apps/788260/header.jpg?t=1651085347 ; logo: https://clan.fastly.steamstatic.com/images/32887519/f5ea7fecc75e46fdd88f46955939155e9270b4e0.png ; halaman sumber: https://steamcommunity.com/app/788260 . Aset game tetap milik pemilik merek masing-masing.
- Nama member (member_name) terpisah dari label key. Data lama di-backfill dari label, nama member dan license key tampil sebelum durasi pada portal.
- Unduhan web injector tetap membutuhkan session hasil login key dan otorisasi ulang tiap request. Route web DLL member dihapus; DLL hanya lewat GET /api/v1/games/dll dengan Bearer key, X-HWID, X-Game, key aktif/terikat, expiry dan game authorization, checksum SHA256 serta audit.
- Activity log dipadatkan menjadi 8 item per halaman dan lebar konten dibatasi. Tautan admin dan assurance di bawah login member dihapus sesuai screenshot.
- Katalog paket admin: nama, deskripsi, durasi hari, harga integer IDR dan akses game. Member dapat memesan dan melihat status/key baru; identitas order terikat session member.
- Snapshot harga/durasi/game saat order; token idempotensi, verifikasi manual admin, penerbitan key dan ledger satu transaksi DB. Pending/cancelled bukan pendapatan; key hasil pembelian belum aktif sebelum aktivasi injector.
- Ledger IDR integer memisahkan bruto, fee dan pengeluaran; void audit tanpa hard delete. Transaksi order tidak bisa di-void melalui ledger umum.
- Grafik keuangan, total laporan dan XLSX memakai FinanceReportService yang sama, rentang tanggal pembayaran WIB. Dua sheet Excel: Ringkasan dan Transaksi; angka numerik, teks aman dari formula injection, XML/ZIP valid.
- Payment gateway belum ditentukan: tidak ada transaksi gateway fiktif, endpoint pembayaran publik ataupun status lunas dari client. Kolom source/external_reference tersedia untuk integrasi terverifikasi nanti.
- Feed notifikasi admin/member terisolasi; pembaruan otomatis tiap 10 detik, unread tersimpan per scope/browser. Konten disegarkan tanpa menimpa dialog/form/teks terseleksi; pembaruan ditunda sampai interaksi selesai.
- 19 tes fitur baru / 156 assertions tahap awal lolos (rekonsiliasi uang/export, idempotensi, batas akses, snapshot order, artwork, DLL API dan feed notifikasi). Verifikasi visual/regresi akhir sedang berjalan.


### Verifikasi backend dan perbaikan visual
- Regresi SQLite: 94 tests / 629 assertions lolos.
- Regresi MySQL keyforge_test: 94 tests / 629 assertions lolos setelah memperbaiki panjang referensi fixture order (factory UUID awal melebihi varchar40; referensi produksi tidak terdampak).
- Browser: kartu game bergambar center, font baru, popup hapus custom (getJsDialog kosong), tombol Batal tidak mengeksekusi penghapusan, panel notifikasi dan halaman keuangan berhasil dirender.
- Ditemukan posisi dialog mengikuti reset margin Tailwind dan stacking panel notifikasi. Diperbaiki dengan centering dialog eksplisit dan layer header. Activity log kini maksimum tinggi 410px + pagination 8 item.
- Pemasukan aktual di database aplikasi tidak ditambah untuk QA; tes uang/order dijalankan hanya di database pengujian terisolasi.


### Uji browser lanjutan
- Dialog custom sekarang center; Batal dan submit akhir diuji memakai key QA Popup Disposable yang dibuat khusus lalu soft-delete. Tidak ada key pengguna yang dihapus/direset.
- Key uji dibuat dengan nama member terpisah (QA Popup Member) dan label key berbeda. Toast sukses muncul setelah create/delete.
- Tab observer otomatis menghapus baris key uji tanpa reload manual setelah perubahan dari tab lain; tombol konfirmasi masih berfungsi setelah DOM diperbarui.
- Tema light pada admin/member, urutan nama member → license key → durasi, tombol Beli key, empty state katalog dan feed notifikasi diverifikasi.
- Tidak ada error console JavaScript pada portal/shop saat QA.
- Preview viewport pada tab member tidak mengikuti ukuran yang diminta tool; render mobile admin/notifikasi teramati pada tab observer. Semua override viewport di-reset dan tab QA ditutup.
- Katalog dapat diedit dan disembunyikan; perubahan paket tidak mengubah snapshot pesanan lama. Member dapat membatalkan hanya pesanan pending miliknya.
- Aset header ROS bawaan sudah memuat logo; overlay logo tambahan dilewati untuk kombinasi default agar tidak menumpuk. Upload logo/banner kustom tetap ditampilkan.


## Hasil akhir tahap upgrade — 27 November 2026
- PHPUnit SQLite: 95 tests / 636 assertions, seluruhnya lolos.
- PHPUnit MySQL keyforge_test: 95 tests / 636 assertions, seluruhnya lolos.
- Vite build berhasil, Blade cache berhasil, Pint selesai. Semua migrasi database aplikasi berstatus Ran.
- Endpoint API tervalidasi: POST /api/v1/licenses/validate; GET /api/v1/licenses/status; GET /api/v1/games/dll.
- Health HTTP login member lokal: 200; server tetap berjalan di http://127.0.0.1:8000.
- QA browser membuat satu key sementara QA Popup Disposable, kemudian menghapusnya melalui dialog custom; data key pengguna tidak diubah. Audit test tetap tersimpan.
- Tidak ada transaksi finansial atau produk harga contoh yang dimasukkan ke database aplikasi.
- Payment gateway, refund dan integrasi injector asli belum diaktifkan; tahap ini menyelesaikan website lokal, API distribusi terotorisasi, pembelian manual terlacak dan dasar rekonsiliasi.
- Pengguna perlu refresh halaman lama sekali untuk memuat bundle CSS/JS baru; sesudah itu data/notifikasi diperbarui otomatis tiap 10 detik saat tab aktif, countdown setiap detik.


## 27 November 2026 — Penataan kelompok grafik dashboard
- Grafik arus keuangan dan aktivitas validasi dikelompokkan dalam dua kartu berdampingan dengan lebar seimbang pada desktop.
- Grafik keuangan tidak lagi menjadi satu kartu penuh selebar dashboard. Tinggi kartu mengikuti baris yang sama.
- Panel API dipadatkan menjadi baris informasi di bawah kedua grafik.
- Pada tablet/ponsel grafik turun menjadi satu kolom agar label tetap terbaca. Rumus, sumber transaksi, filter dan data grafik tidak diubah.
- Verifikasi build, render dan visual dilakukan sesudah perubahan.

- Verifikasi selesai: build Vite dan compile Blade berhasil; 13 tes AdminDashboard / 95 assertions lolos. Browser menunjukkan dua kartu grafik sejajar pada posisi Y yang sama, masing-masing lebar 468px dan tinggi 430px pada viewport desktop uji; baris API 141px di bawahnya.


## 27 November 2026 — Penyempurnaan admin/member, invoice dan hardening keamanan

### Navigasi, tampilan dan keterbacaan
- Konfigurasi payment gateway dipindahkan dari Penjualan & pembayaran ke Pengaturan bersama Pengaturan API. Breadcrumb mengikuti kelompok Pengaturan.
- Daftar paket admin menampilkan deskripsi, harga, durasi, akses game, status katalog dan tombol yang dikelompokkan. Artwork game memakai lapisan gambar/gradasi kiri, tengah dan kanan; aset yang sudah tersedia digunakan kembali.
- Checkout ditata ulang menjadi ringkasan invoice dan area pembayaran, dengan nominal utama, indikator tahap, status, gradasi warna, animasi ringan dan tata letak satu kolom pada layar kecil. prefers-reduced-motion tetap dihormati.
- Ikon kontekstual diperluas ke admin, member, shop, filter, judul, tombol, header tabel dan popup detail. Ikon dekoratif tidak menggantikan label teks; pembaruan DOM/live tetap didukung tanpa menggandakan ikon yang sudah ada.
- Ukuran teks admin/member/shop/checkout diseimbangkan: isi utama sekitar 14–15px, teks bantuan sekitar 12px dan label kompak sekitar 11–12px. Judul, nominal dan navigasi mempertahankan hierarki.
- Revisi tabel pesanan terakhir: identitas pesanan dipadatkan menjadi tiga baris, isi kolom rata atas, nominal rata kanan, header berikon konsisten; Verifikasi lunas menjadi tindakan utama dengan Batalkan/Hapus dua kolom seimbang di bawahnya. Tombol tidak lagi membungkus secara acak.

### URL dan penghapusan invoice
- URL publik checkout/status/payment memakai nomor invoice melalui binding order:number, bukan ID numerik. Tautan dan URL kembali yang dikirim saat membuat invoice mengikuti nomor tersebut.
- Nomor invoice bukan pengganti otorisasi. Receipt/status/payment tetap dibatasi ke sesi pemilik guest/member atau admin; nomor yang diketahui pihak lain tidak memberikan akses.
- Koreksi pengguna: hapus manual HANYA di admin pada Pesanan lisensi. Route dan controller penghapusan checkout publik dihapus; tombol hapus serta informasi penghapusan otomatis tidak ditampilkan di checkout/member.
- Invoice dapat dihapus hanya jika masih pending, belum dikirim ke gateway, belum mempunyai ID/URL/status gateway, belum menerbitkan key, belum dibayar dan belum mempunyai ledger keuangan. Pemeriksaan diulang setelah row lock untuk menghindari benturan dengan proses pembayaran.
- Perintah invoices:prune membersihkan invoice yang memenuhi syarat setelah 24 jam. Invoice lunas atau yang pernah diproses gateway tetap dilindungi; tidak otomatis dihapus hanya berdasarkan umur.
- Jadwal Laravel berjalan setiap menit dengan withoutOverlapping. Start-Local.ps1 menjalankan/menggunakan ulang worker scheduler tersembunyi dan mencatat PID/log di .local. Eksekusi terjadwal berhasil teramati; dry-run terakhir tidak menemukan invoice yang perlu dihapus.

### Status pembayaran dan batas Sandbox
- Checkbox aktif dan API key saja belum membuat gateway siap. Halaman pengaturan/checkout kini membedakan gateway nonaktif, API key belum tersedia, webhook belum dikonfigurasi dan Production yang tidak tersedia di lingkungan local.
- Status kredensial per lingkungan menampilkan kelengkapan API key + signing secret/token tanpa membuka nilainya. Kolom rahasia kosong mempertahankan nilai yang sudah tersimpan terenkripsi.
- Pemeriksaan terakhir: mode Sandbox, pembayaran online aktif, tetapi verifikasi webhook Sandbox belum dikonfigurasi sehingga gateway belum siap. Endpoint webhook ada pada POST /api/payments/sumopod/{mode}; alamat loopback lokal tidak bisa menerima callback internet tanpa hosting/tunnel publik yang sesuai.
- Production yang terverifikasi menerbitkan satu key atau memperpanjang key tujuan, lalu mencatat ledger satu kali. Redirect sukses sendiri tidak mengesahkan pembayaran.
- Sandbox saat ini tetap simulasi: tidak menerbitkan key yang dapat dipakai, tidak memperpanjang lisensi dan tidak mencatat pendapatan riil. Permintaan pilihan key uji Sandbox belum diputuskan/diimplementasikan; jangan menganggap fitur tersebut sudah tersedia.

### Pemeriksaan dan penguatan keamanan
- Seluruh password akun admin yang tersimpan diperiksa sebagai hash bcrypt; biaya konfigurasi runtime 12. Model User memakai cast hashed dan menyembunyikan password/remember_token dari serialisasi. Tidak ada password akun yang diganti selama hardening ini.
- Input email/key berbentuk array tidak lagi menimbulkan error konversi sebelum validasi pada limiter admin/member/API. Fingerprint limiter memakai hash; login admin dibatasi per akun lintas IP, per pasangan akun/IP dan per IP.
- Validasi pembuatan password admin menolak lebih dari 72 byte dan karakter null sebelum bcrypt. Password berkarakter null juga ditolak pada login.
- Login/logout member mengganti ID sesi sambil menghancurkan penyimpanan sesi lama. Sesi terenkripsi, cookie HttpOnly dan pembatasan SameSite tetap dipakai.
- Content Security Policy ditambahkan dengan nonce baru per respons untuk aset Vite/theme. Kebijakan membatasi skrip dan koneksi ke origin sendiri, melarang handler skrip inline, object, base URL dan framing, serta membatasi form ke origin sendiri. Style inline tetap diizinkan karena komponen grafik/kontrol memakai style dinamis; CSP dilewati khusus Vite HMR lokal agar development tetap berfungsi.
- Header tambahan: Cross-Origin-Opener-Policy same-origin dan X-Permitted-Cross-Domain-Policies none. nosniff, DENY framing, no-referrer, no-store/private dan pembatasan permissions tetap dipertahankan.
- Pada Production, Host harus sesuai APP_URL; debug dipaksa nonaktif, secure/HttpOnly/encrypted session dipaksa aktif, dan URL aplikasi menggunakan HTTPS. HSTS hanya dikirim pada respons HTTPS Production. Konfigurasi reverse proxy/sertifikat aktual tetap harus diperiksa saat deployment.
- HTTP client SumoPod tidak mengikuti redirect agar header API key tidak ikut diteruskan ke lokasi lain. Respons redirect tidak dianggap sukses; invoice tetap menunggu rekonsiliasi.
- Otorisasi admin/member, isolasi invoice/record, CSRF, escaping HTML, file upload/unduhan privat, binding HWID dan expiry, serta signature/nominal/idempotensi webhook diperiksa melalui pengujian yang relevan.
- Seeder tidak membuat akun admin bawaan. .env dan .local diabaikan Git; berkas privat disimpan di storage/app/private dengan public serving dimatikan. Berkas kredensial operasional lokal tetap berada di luar public dan tidak disalin ke catatan ini.

### Hasil verifikasi akhir
- PHPUnit SQLite in-memory: 142 tes / 1.061 assertions, seluruhnya lolos.
- PHPUnit MySQL khusus keyforge_test: 142 tes / 1.061 assertions, seluruhnya lolos. Database aplikasi tidak digunakan untuk tes regresi.
- Tambahan tes mencakup nomor invoice, akses hapus khusus admin, batas tepat 24 jam, invoice gateway/ledger yang dilindungi, kondisi stale, penerbitan key, nonce/header keamanan, Host Production, CSRF, escaping, input credential salah tipe, pembatasan login lintas IP, hash/validasi password, penghancuran sesi lama dan redirect gateway.
- Composer audit: tidak ada advisory atau paket abandoned yang dilaporkan. npm audit: 0 kerentanan yang diketahui pada saat pemeriksaan. Tidak ada dependency yang ditambah/di-upgrade.
- Vite production build, kompilasi Blade dan Pint terarah berhasil. Folder tidak memakai Git sehingga Pint --dirty tidak tersedia; pemformatan dibatasi ke file PHP yang diedit.
- Batas verifikasi: hardening dan tes lokal bukan jaminan bebas celah atau pengganti penetration test. TLS/reverse proxy, firewall, backup/restore, hak akses server, monitoring produksi dan MFA belum dipasang atau diverifikasi pada hosting publik. Pemeriksaan visual tabel admin belum dikonfirmasi pada revisi terakhir; status pemeriksaan toko publik dijelaskan di bagian berikutnya.
- Tidak ada secret/API key/password yang dicatat di WORKLOG; tidak ada pembayaran gateway sungguhan atau deployment publik yang dijalankan.

### Pemeriksaan browser dan batas QA tambahan
- Browser membuka toko publik pada server utama lokal. Tema light, ikon, filter dan keterbacaan halaman teramati; tidak ada error JavaScript yang dilaporkan pada halaman tersebut setelah CSP diterapkan.
- Helper QA lama pada port loopback 8001 gagal memuat index.php karena konfigurasi root lama. Upaya menghentikan/memulai ulang helper tersebut ditolak oleh pemeriksaan persetujuan otomatis sebelum perintah dijalankan; alasan terperinci tidak diberikan. Tindakan yang ditolak tidak dicoba ulang melalui cara lain.
- Pemeriksaan dilanjutkan secara baca-saja ke server utama tanpa menghentikan layanan atau mengubah pesanan. Pemeriksaan visual tabel admin belum dikonfirmasi melalui browser; pengujian rendering halaman dan fungsi backend tetap lulus.

## Catatan lengkap untuk melanjutkan pekerjaan — 27 November 2026 (WIB)

Bagian ini melengkapi riwayat sebelumnya dan menjadi ringkasan keputusan terakhir pengguna. Hasil tes di bawah adalah hasil pengujian terakhir yang sudah dijalankan, bukan pengujian ulang pada saat penulisan catatan ini.

### Arahan pengguna yang harus dipertahankan
1. Nama merek KazuhaDev.; project tetap di D:\tester\keyforge. Website lokal dahulu, deployment publik belum dilakukan. Injector asli tidak diubah.
2. Payment gateway mempunyai menu sendiri pada Pengaturan, terpisah dari Penjualan & pembayaran. Pengaturan API berada di kelompok Pengaturan yang sama.
3. Tampilan harus rapi, berwarna, mempunyai animasi yang sesuai, dan tidak terasa flat. Ikon mencakup admin, member, shop, checkout, menu, filter, tombol, tabel dan detail; jangan membatasi ikon hanya ke popup detail.
4. Ukuran teks harus nyaman dibaca dan seimbang pada semua halaman, termasuk admin/member/shop/checkout. Jangan mengecilkan kembali teks untuk memaksakan tabel muat; sediakan pengguliran tabel pada layar sempit.
5. Kartu paket harus menjelaskan harga, durasi, deskripsi dan akses game, dengan artwork/gradasi game di kiri, tengah dan kanan. Gunakan aset game yang sudah tersedia.
6. Tabel pesanan tidak boleh memiliki tombol yang membungkus secara acak atau kolom dengan posisi vertikal yang tidak seimbang. Revisi terakhir memakai satu tindakan utama dan dua tindakan sekunder sejajar.
7. URL checkout publik harus memakai nomor invoice, bukan ID database. Otorisasi pemilik sesi tetap wajib; nomor invoice sendiri bukan tiket akses.
8. HAPUS MANUAL INVOICE HANYA DI ADMIN. Jangan menambahkan kembali tombol/endpoint hapus pada checkout publik maupun member.
9. Jangan menampilkan pemberitahuan penghapusan otomatis 24 jam di checkout/member. Pembersihan tersebut bekerja di belakang layar.
10. Invoice belum dibayar dan belum pernah dikirim ke gateway dapat dihapus manual oleh admin atau otomatis setelah 24 jam. Invoice berproses/menunggu gateway, lunas, memiliki key hasil pembelian atau ledger harus dilindungi.
11. Selama pengajuan merchant belum selesai, gunakan Sandbox. Jangan mengaktifkan Production atau menganggap simulasi sebagai pendapatan riil.
12. Setelah pembayaran Production terverifikasi, pembeli menerima key di halaman pesanan; perpanjangan memakai key yang sama. Redirect sukses tidak boleh langsung menerbitkan key.
13. Pengguna meminta seluruh keamanan aplikasi diperkuat dan password admin dipastikan di-hash. Jangan menjanjikan keamanan absolut; catat bukti pengujian dan batas verifikasinya.
14. Pengguna meminta SEMUA arahan, revisi, hasil dan pekerjaan tertunda dicatat. Jangan menyertakan password, API key, signing secret, token webhook, APP_KEY atau nilai kredensial lainnya dalam dokumentasi.

### Peta file perubahan
- resources/views/components/layout.blade.php: kelompok menu admin dan ikon navigasi.
- resources/views/orders.blade.php: tabel pesanan, pengelompokan tindakan, form hapus khusus admin.
- resources/views/products.blade.php: kartu paket terperinci dan lapisan artwork game.
- resources/views/checkout.blade.php: checkout baru, status/pilihan pembayaran, key setelah lunas, tanpa hapus/notice pembersihan publik.
- resources/views/member/shop.blade.php: riwayat pesanan member, tanpa kemampuan hapus permanen.
- resources/views/payment-settings.blade.php dan resources/views/settings.blade.php: konfigurasi di kelompok Pengaturan dan status kelengkapan gateway.
- resources/views/components/icon.blade.php dan resources/js/ui-icons.js: pustaka ikon dan dekorasi semantik yang aman terhadap teks tidak tepercaya; ikon tidak digandakan saat pembaruan live.
- resources/css/app.css: kartu paket, checkout, animasi, ikon, skala tipografi dan perapian tabel terbaru. Bundle production sudah dibangun kembali.
- app/Models/PurchaseOrder.php: pemeriksaan kelayakan hapus dan scope invoice yang dapat dibersihkan.
- app/Services/PurchaseService.php: penghapusan invoice dengan transaksi/row lock dan perlindungan ledger/gateway.
- app/Http/Controllers/PurchaseController.php: endpoint hapus khusus admin dan pengelolaan pesanan.
- app/Http/Controllers/StorefrontController.php: receipt/status/payment dengan otorisasi pemilik; tidak memiliki aksi destroy publik.
- routes/web.php: checkout memakai order:number, DELETE invoice hanya di grup auth + EnsureAdmin.
- app/Console/Commands/PruneUnpaidInvoices.php dan routes/console.php: pembersihan invoice setiap menit setelah batas 24 jam, dengan opsi --dry-run.
- Start-Local.ps1: server utama, MySQL lokal dan worker scheduler; proses dijalankan tersembunyi dan digunakan ulang jika sudah aktif.
- app/Support/PaymentSettings.php dan app/Http/Controllers/PaymentSettingsController.php: pemeriksaan kesiapan gateway beserta alasan yang tepat; nilai secret tetap terenkripsi.
- app/Services/SumoPodService.php: pembuatan invoice gateway, URL kembali ke nomor invoice, tidak mengikuti redirect, dan penanganan status uncertain.
- app/Http/Controllers/SumoPodWebhookController.php: verifikasi webhook dan pemisahan Sandbox/Production; tidak diubah untuk menerbitkan key uji.
- app/Http/Middleware/SecurityHeaders.php dan resources/views/components/theme-head.blade.php: CSP nonce, header keamanan, validasi Host Production dan HSTS pada HTTPS.
- app/Providers/AppServiceProvider.php: limiter aman untuk input salah tipe, pembatasan akun lintas IP dan konfigurasi Production yang diperketat.
- app/Http/Controllers/MemberController.php: rotasi ID sesi yang menghancurkan sesi lama saat login/logout.
- app/Http/Requests/StoreAdminRequest.php dan LoginRequest.php: validasi password sebelum hashing/autentikasi.
- config/session.php: default session terenkripsi dan secure cookie untuk Production.
- tests/Feature/InvoiceLifecycleTest.php, PaymentSafetyTest.php dan SecurityHardeningTest.php: regresi invoice, otorisasi, pembayaran dan hardening; tes lain dalam suite juga tetap dijalankan.

### Alur dan alamat penting
- Admin lokal: http://127.0.0.1:8000/login; pesanan: /orders; paket: /products; pembayaran: /payment-settings; API: /settings; toko publik: /shop; member: /member/login.
- Checkout baru: GET /checkout/{nomor_invoice}; status: GET /checkout/{nomor_invoice}/status; buat invoice gateway: POST /checkout/{nomor_invoice}/payment.
- Hapus manual oleh admin: DELETE /orders/{id_internal}; tidak ada DELETE /checkout/{nomor_invoice}.
- Webhook Sandbox: POST /api/payments/sumopod/sandbox; Production memakai suffix /production. Bukan alamat redirect browser.
- Aplikasi mengirim success_return_url dan cancel_return_url ke receipt invoice yang sama. Default redirect /shop hanya cadangan untuk pengujian browser pada komputer lokal yang sama.
- Alur: pilih paket → simpan pesanan/snapshot → buat invoice gateway → bayar/simulasikan pada SumoPod → webhook terverifikasi → proses status/key sesuai lingkungan → halaman receipt menyegarkan status.
- Jalankan lokal: .\Start-Local.ps1. Periksa rencana pembersihan tanpa menghapus: php artisan invoices:prune --dry-run. Jadwal memakai scheduler Laravel, bukan automasi chat.
- Untuk PHP CLI pada komputer ini, sys_temp_dir/upload_tmp_dir perlu diarahkan ke .local/php-tmp bila konfigurasi PHP lama menunjuk direktori drive F yang sudah tidak tersedia.
- Tes default memakai SQLite in-memory. .\Test-MySQL.ps1 mengisolasi tes ke keyforge_test di loopback port 3307; jangan menjalankan RefreshDatabase pada database aplikasi.

### Bukti terakhir dan hal yang belum selesai
- SQLite dan MySQL pengujian: masing-masing 142 tes / 1.061 assertions lolos. Vite build, Blade dan Pint terarah berhasil; Composer/npm audit tidak melaporkan kerentanan yang dikenal saat diperiksa.
- Runtime admin: hash bcrypt dengan biaya konfigurasi 12; debug lokal nonaktif; session terenkripsi dan HttpOnly. Tidak ada password akun yang diganti atau rahasia yang dirotasi.
- Hasil pemeriksaan toko publik: respons HTTP 200, CSP aktif, frame DENY, nosniff, dan tidak ada error JavaScript teramati. Ini belum membuktikan semua halaman/viewport bebas cacat visual.
- Tabel admin revisi terakhir sudah diubah dan diuji rendering/fungsinya, tetapi visual browsernya belum dikonfirmasi. Helper QA port 8001 mempunyai konfigurasi root lama yang gagal; perintah penghentian/restart ditolak pemeriksaan persetujuan otomatis dan tidak dieksekusi. Jangan menganggap helper itu sehat atau melaporkan perbaikan yang belum dilakukan.
- Gateway terakhir diperiksa masih Sandbox aktif, API key tersimpan, verifikasi webhook Sandbox belum diisi. Periksa ulang status nonrahasia bila pekerjaan dilanjutkan karena pengguna dapat mengubah konfigurasi setelah catatan ini.
- Pengajuan merchant/KYC belum dinyatakan selesai. Teks bantuan pengisian KYC sudah diberikan di chat, tetapi tidak ada formulir pihak ketiga yang dikirim atau disetujui oleh agen. Identitas usaha, fungsi produk, estimasi transaksi/pendapatan harus mengikuti kenyataan pengguna.
- Penerbitan key yang benar-benar dapat dipakai pada Sandbox BELUM diputuskan. Pertanyaan key uji fungsional lokal versus contoh key belum dijawab; implementasi saat ini tetap tanpa key/perpanjangan/pendapatan riil untuk Sandbox.
- Domain tujuan ros.kazuhadev.com belum diverifikasi melayani aplikasi ini; callback internet membutuhkan URL HTTPS publik yang benar. Tidak ada deploy, tunnel, sertifikat atau pembayaran sungguhan yang dibuat pada pekerjaan ini.
- Deployment/keamanan operasional yang masih perlu dikerjakan atau diverifikasi: TLS dan reverse proxy tepercaya, firewall, hak akses server/database, backup/restore, monitoring/rate limit infrastruktur dan MFA admin. Hardening lokal bukan jaminan bebas celah atau penetration test penuh.
- Tidak ada data key pengguna atau transaksi riil yang sengaja dihapus/diubah untuk QA. Pruner dry-run terakhir menunjukkan 0 invoice memenuhi syarat. Penghapusan otomatis hanya mengikuti aturan umur/kondisi invoice yang sudah disepakati.


## 27 November 2026 — DLL per versi dan kontrak akun client

- Arahan pengguna: satu game memiliki beberapa versi DLL bernama, seperti Infinity/Mirae, masing-masing file sendiri. Versi dapat ditambah pada Konfigurasi Game; aplikasi desktop nantinya mendeteksi launcher dan meminta DLL yang tepat.
- Migrasi additive menambah games.dll_variants (JSON nullable). Service mengunci game, menyimpan file privat/checksum/ukuran, mengganti dan menghapus file versi tertentu sesudah commit; rollback menjaga file lama. Request edit yang tidak menyertakan versi lain tidak menghapusnya.
- Form menyediakan Tambah versi game, nama, kode permanen dan upload DLL; versi lama dapat diganti nama/berkas atau ditandai hapus. Maksimal 20 versi, setiap DLL 20 MB. Kolom DLL lama dipertahankan untuk kompatibilitas data.
- API GET /api/v1/games/dll menerima X-Game-Version. infinity hanya mendapat Infinity; mirae hanya mendapat Mirae. Unknown/missing-file -> 404 release_unavailable; tanpa header pada game bervarian -> 422 game_version_required. Tidak fallback otomatis. Game tanpa varian tetap dapat memakai kontrak lama tanpa header.
- Request tetap memerlukan Bearer key + X-HWID + X-Game, lisensi aktif/bound dan akses game. Respons binary menambah X-Game-Version dan checksum sesuai rilis. Audit mencatat versi/hash tanpa path privat.
- Arahan lanjutan pengguna: DLL mengambil data akun (expired, key, nama user, paket). POST validate dan GET status kini menambah account.member_name, key_hint, package snapshot pesanan lunas terakhir dan games. Paket sandbox/pending diabaikan; manual tanpa pesanan null. Key lengkap tidak dikembalikan karena client sudah memilikinya; expired/HWID salah tetap ditolak tanpa data akun.
- CLIENT_API.md memuat kontrak, error, contoh header, aturan client/DLL, batas kemampuan server dan prosedur update. API console diperbarui. Source/binary injector dan DLL tidak diubah pada tahap server ini.
- Verifikasi SQLite: 149 tes / 1.147 assertions PASS. Tes awal unduhan/varian: 16 tes / 151 assertions PASS. Vite build dan Blade cache berhasil; Pint targeted berhasil (--dirty tidak tersedia karena folder belum repository Git).
- Pengguna meminta push ke https://github.com/ksmaajr/KazuhaDev-Ksmarjaa. Repo terverifikasi dapat diakses dan hanya mempunyai README, default main. Pengguna kemudian meminta dari lokal dahulu karena agent masih deploy; PUSH DITAHAN. Belum init Git/commit/push atau mengubah server remote. Siapkan update sebagai commit terpisah setelah baseline deploy jelas; jangan menyatakan git pull tersedia sebelum push selesai.
- Migrasi kolom dll_variants berhasil diterapkan pada database aplikasi lokal. Tidak menghapus atau mengunggah binary pengguna. Server publik/agent deployment tidak disentuh.
- Verifikasi MySQL keyforge_test selesai: 149 tes / 1.147 assertions PASS. Suite menggunakan database pengujian terisolasi melalui Test-MySQL.ps1.

### Revisi form DLL — 27 November 2026
- Pengguna meminta kolom DLL lama / tanpa versi dihapus dari tampilan. Form create/edit kini hanya menawarkan DLL per versi game; kartu game juga tidak lagi menampilkan baris DLL tanpa versi.
- Data/file DLL lama dan kompatibilitas API tetap dipertahankan; tidak ada penghapusan berkas atau database. Dokumentasi CLIENT_API disesuaikan. Push masih ditahan.

### Persiapan push GitHub atas instruksi terbaru pengguna
- Pengguna mengizinkan langsung push proyek Laravel terbaru ke repo agar agent dapat pull update.
- Repository lokal diinisialisasi dan riwayat README remote dipertahankan. Commit cdd8da0 mengimpor baseline persis dari ZIP source awal (dependency, cache runtime, database, .env dan unggahan privat dikecualikan).
- Update DLL versi, data akun dan dokumentasi disiapkan sebagai commit berikutnya. CLIENT_API memuat instruksi pull serta penanganan server awal berbasis ZIP. Tidak melakukan deploy/server restart atau mengirim pesan ke agent lain.
