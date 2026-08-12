# TODO - STC 2026 Competition Registration Forms (Phase 2)

## Tujuan
Memperbaiki semua form pendaftaran lomba agar:
- Menggunakan proper `name=""` attributes di semua input
- Menggunakan `data-submit` + `data-competition` attributes
- Tidak menggunakan `alert()` atau `onsubmit` inline
- Mengirim data via `FormData` + `fetch()` ke backend API
- Menambahkan CSS submission.css dan payment.css

## Status

### Individual Competitions (Done)
- [x] **Web Design** — `data-competition="web_design"`
- [x] **Infografis** — `data-competition="infografis"`
- [x] **Microsoft Excel** — `data-competition="excel"`
- [x] **Speed Typing** — `data-competition="speed_typing"`

### Team Competitions (Done)
- [x] **Cerdas Cermat** — `data-competition="cerdas_cermat"` (fixed 3 members)
- [x] **Mobile Legends** — `data-competition="mobile_legends"` (dynamic members with sequential naming)
- [x] **Free Fire** — `data-competition="free_fire"` (dynamic members with sequential naming)

### Supporting Files (Created)
- [x] `page_Competition/js/mobile-legends.js` — Updated with sequential player naming
- [x] `page_Competition/js/freefire.js` — Updated with sequential player naming
- [x] `JavaScript/form-common.js` — Common form utilities
- [x] `JavaScript/submission.js` — Form submission handler using fetch()

### Landing Page Fix (Done)
- [x] **Landing_page/Index.html** — Fixed corrupted file with unresolved Git merge conflict markers (`<<<<<<<`, `=======`, `>>>>>>>`) that caused the entire page to render broken/messy. Resolved all conflicts (kept the complete "Updated upstream" version), fixed CSS link to `/desain/style.css`, and restored all sections (Hero sky theme, About, Sponsors, Competitions with working "Daftar Sekarang" links, Prize, Timeline, CTA, Footer).

### Frontend Fix (Done — 12 Agustus 2026)
- [x] Semua link `Landing_page/Index.html` → `index.html`; path absolut (`/desain`, `/foto`, `/JavaScript`) → relatif
- [x] `desain/global.css` ditambahkan ke 7 halaman lomba (sebelumnya latar transparan → teks tak terbaca di browser mode gelap)
- [x] `desain/dashboard.css`, `desain/announcement.css`, `desain/gallery.css` dibuat (sebelumnya di-link tapi tidak ada)
- [x] `multi_page/gallery.html` dibuat (sebelumnya file kosong 0 byte, padahal di-link dari semua navbar & footer)
- [x] Aset `foto/` yang hilang dibuat sebagai placeholder (7 ikon lomba, 4 slot sponsor); ornamen sponsor memakai `hiasan web.png`
- [x] `dashboard.html` — `#greet` membungkus `#subGreet` sehingga `textContent` menghapusnya (TypeError di `dashboard.js:18`)
- [x] `web Design.html` & `Infografis.html` — id hasil pendaftaran diselaraskan ke `#submitResult` / `#resultCode` sesuai `submission.js`
- [x] `.submission-result` diberi `display:none` (sebelumnya kotak "PENDAFTARAN BERHASIL" tampil permanen) + styling `.code-display`
- [x] `.preview` diberi `display:none` (ikon gambar rusak sebelum peserta unggah berkas)
- [x] `speed typing.html` — judul tab salah ("Cerdas Cermat")

### Performa & Mobile (Done — 12 Agustus 2026)
- [x] **`foto/LOGOSTC.png` 23994×15950 px / 9.4 MB → 512×347 / 114 KB.** Logo ini dipakai 3–5 kali
      per halaman tapi hanya tampil 110–230 px. Browser harus mendekode ~380 megapiksel
      (±1,5 GB memori mentah) tiap perpindahan halaman — ini penyebab utama lag.
      `hiasan web.png` 1,5 MB → 193 KB; foto galeri 1600 px → 900 px.
      **Total per halaman: 11,0 MB → 412 KB (−96%).** Berkas asli disimpan di `foto/_original/`.
- [x] Preloader dipangkas dari 1400 ms + 700 ms fade → 600 ms + 400 ms
- [x] `loading="lazy"` + `decoding="async"` untuk gambar di bawah layar pertama
- [x] Aturan navbar mobile dipusatkan di `desain/navbar.css` — sebelumnya hanya ada di
      `responsive.css` yang **tidak dimuat** 6 halaman lomba, jadi hamburger tidak pernah muncul di sana
- [x] Tombol **Login masuk ke dalam hamburger** pada layar ≤900 px (`.nav-login-item`)
- [x] Breakpoint CSS (dulu 768 px) diselaraskan dengan JavaScript (900 px)
- [x] `navbar.js` — menekan "Competitions" di mobile langsung menutup seluruh menu

### Form & Konten (Done — 12 Agustus 2026)
- [x] **`web Design.html`** — form tidak punya `data-submit`/`data-competition`, jadi `submission.js`
      tidak pernah terpasang: submit hanya me-reload halaman, data tidak terkirim.
      Nama field juga beda sendiri (Indonesia) dan sekarang disamakan dengan lomba lain
      (`name`, `student_id`, `school_name`, `class`, `phone`, `student_card`, `payment_proof`,
      `payment_method`, `amount`, `transaction_date`, `agree_*`), tombol → `btn-submit`
- [x] **`speed typing.html`** — `data-competition="cerdas_cermat"`, semua pendaftaran Speed Typing
      akan tercatat sebagai Cerdas Cermat. Diperbaiki ke `speed_typing`
- [x] `mobile legend.html` — id form `freeFireForm` → `mobileLegendsForm`
- [x] Ikon YouTube dihapus dari footer 9 halaman (Instagram, WhatsApp, Email tetap)

### Keamanan (Done — 12 Agustus 2026)
- [x] **`create_admin.php` dinonaktifkan.** Skrip ini bisa dibuka siapa saja di
      `http://.../stc2026-backend/create_admin.php` dan langsung membuat akun **admin**
      berpassword `admin123` — pintu belakang penuh ke seluruh data pendaftar.
      Dipindah ke `_arsip_lokal/` (ditolak `.htaccess`)
- [x] 11 skrip `test_*.php` + `admin/session_test.php` diarsipkan (membocorkan session ID & isi database)
- [x] **Admin dashboard tidak lagi dijaga `localStorage`.** Sebelumnya cukup jalankan
      `localStorage.setItem('stc_user','{"role":"admin"}')` di console untuk membukanya.
      Sekarang `JavaScript/auth-guard.js` menanyakan ke `auth/me.php?admin=1`, yang membaca
      session PHP + tabel `users`. Sudah diuji: localStorage palsu ditolak
- [x] **`admin/registrations.php`** hanya cek "sudah login", bukan role admin — akun peserta
      biasa bisa melihat data seluruh pendaftar. Sekarang `require_admin()`
- [x] `config/guard.php` — session aman (httponly, samesite, secure otomatis saat HTTPS),
      `require_login()` / `require_admin()` yang selalu membaca role dari database
- [x] Pembatas percobaan login: 8 kali gagal per 15 menit per IP+email, disimpan sebagai file
      (bukan session, yang bisa direset dengan hapus cookie) + hash palsu agar waktu respons
      sama untuk email terdaftar dan tidak terdaftar
- [x] Upload: isi berkas diverifikasi `finfo` (bukan cuma ekstensi), izin folder 0777 → 0755,
      `.htaccess` di `uploads/` mematikan eksekusi PHP dan memaksa unduh
- [x] `tools/buat_admin.php` — pengganti CLI-only untuk menambah admin (tidak bisa diakses web)
- [x] Admin **Rafa** ditambahkan lewat `tools/tambah_admin_rafa.sql` (password bcrypt, bukan teks biasa)

### SQL injection
Seluruh 20 file PHP sudah memakai *prepared statement* + `bind_param`. Query yang memakai
`$conn->query()` semuanya string statis tanpa input pengguna. Tidak ditemukan celah injeksi.

### Remaining Tasks
- [ ] **Jalankan `tools/tambah_admin_rafa.sql`** di database `stc2026` supaya admin Rafa aktif
- [ ] **Sambungkan frontend ke `stc2026-backend/`** — `submission.js`, `dashboard.js`,
      `admin-dashboard.js`, `announcement.js`, `login.js`, `register.js` masih menembak
      `http://localhost/stc2026/api/...` yang sudah dihapus. Pemetaan:
  - `api/users/login.php` → `auth/login.php` · `api/users/register.php` → `auth/register.php`
  - `api/registrations/create.php` → `registration/create.php` + `individual/create.php` /
    `team/create.php` / `documents/create.php` / `payments/create.php`
  - `api/registrations/list.php` → `admin/get_registrations.php`
  - `api/registrations/verify.php` → `admin/update_status.php` + `admin/update_payment_status.php`
  - `api/announcements/*` → **belum ada padanannya** di `stc2026-backend/`
- [ ] **Isi `page_Competition/speed typing.html`** masih salinan Cerdas Cermat: judul hero
      "CERDAS CERMAT", badge "QUIZ · TIM 3 ORANG", form minta nama tim + 3 anggota.
      Speed Typing adalah lomba individu — ketentuan, hadiah, dan formnya perlu ditulis ulang
- [ ] Ganti placeholder di `foto/` (sponsor1–4, 7 ikon lomba) dengan aset final
- [ ] Produksi: `config/database.php` masih `root` tanpa password; pindahkan kredensial ke
      luar direktori web dan aktifkan HTTPS

