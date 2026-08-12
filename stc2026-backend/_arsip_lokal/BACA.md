# Arsip skrip lokal

Isi folder ini **tidak boleh** dijalankan lewat browser.

- `create_admin.php` — dulu bisa dibuka siapa saja di
  `http://.../stc2026-backend/create_admin.php` dan langsung membuat
  akun **admin** dengan password `admin123`. Ini pintu belakang penuh
  ke seluruh data pendaftar. Diarsipkan.
  Untuk menambah admin sekarang, pakai `php tools/buat_admin.php`
  dari terminal (tidak bisa diakses lewat web).

- `test_*.php`, `session_test.php` — skrip uji coba yang membocorkan
  session ID dan isi database ke siapa pun yang membukanya.

`.htaccess` di folder ini menolak semua akses HTTP. Kalau server tidak
memakai Apache, pindahkan folder ini ke luar direktori web.
