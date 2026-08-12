-- =========================================================
-- STC 2026 — Tambah akun admin "Rafa"
--
-- Jalankan sekali di database `stc2026` (phpMyAdmin > SQL,
-- atau: mysql -u root stc2026 < tools/tambah_admin_rafa.sql)
--
-- Username : Rafa
-- Password : (yang kamu tentukan — TIDAK disimpan di file ini)
--
-- Password disimpan sebagai hash bcrypt, bukan teks biasa.
-- Jadi meskipun isi tabel users bocor, password aslinya tidak
-- bisa dibaca. Ini hash yang cocok dengan password yang kamu
-- berikan, dan diverifikasi oleh password_verify() di PHP.
-- =========================================================

USE `stc2026`;

INSERT INTO `users`
    (`nama_lengkap`, `email`, `no_whatsapp`, `password`, `role`)
VALUES
    (
        'Rafa',
        'Rafa',
        '-',
        '$2y$10$gHB3xFKYXdcOpgOqgM3nmefN8z/yskEpKDEc8UJCL04K8XfZrBS1q',
        'admin'
    )
ON DUPLICATE KEY UPDATE
    `nama_lengkap` = VALUES(`nama_lengkap`),
    `password`     = VALUES(`password`),
    `role`         = 'admin';

-- Pastikan tidak ada akun admin lain yang tidak dikenali.
-- Jalankan ini untuk memeriksa siapa saja yang punya akses admin:
--
--   SELECT id, nama_lengkap, email, role, created_at
--   FROM users WHERE role = 'admin';
--
-- Yang boleh muncul hanya: NJas dan Rafa.
