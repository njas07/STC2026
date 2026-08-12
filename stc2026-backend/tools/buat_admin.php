<?php
/* =========================================================
   STC 2026 — TAMBAH / UBAH AKUN ADMIN

   HANYA lewat terminal, tidak bisa dibuka di browser.
   Ini pengganti create_admin.php lama yang bisa diakses
   siapa saja dan membuat admin berpassword "admin123".

   Cara pakai (dari folder stc2026-backend):

       php tools/buat_admin.php "Nama Lengkap" username password

   Contoh:

       php tools/buat_admin.php "Rafa" Rafa "Rafa24@"

   Kalau username sudah ada, password-nya diperbarui dan
   role-nya dipastikan admin.
   ========================================================= */

if (PHP_SAPI !== "cli") {
    http_response_code(403);
    die("Skrip ini hanya boleh dijalankan dari terminal.");
}

require_once __DIR__ . "/../config/database.php";

$nama = $argv[1] ?? null;
$username = $argv[2] ?? null;
$password = $argv[3] ?? null;
$whatsapp = $argv[4] ?? "-";

if (!$nama || !$username || !$password) {
    fwrite(STDERR,
        "Kurang argumen.\n\n" .
        "  php tools/buat_admin.php \"Nama Lengkap\" username password [whatsapp]\n\n"
    );
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "Password admin minimal 8 karakter.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

/* Sudah ada? */
$cek = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$cek->bind_param("s", $username);
$cek->execute();
$ada = $cek->get_result()->fetch_assoc();
$cek->close();

if ($ada) {
    $stmt = $conn->prepare("
        UPDATE users
        SET nama_lengkap = ?, password = ?, role = 'admin'
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $nama, $hash, $ada["id"]);
    $aksi = "diperbarui";
} else {
    $stmt = $conn->prepare("
        INSERT INTO users
        (nama_lengkap, email, no_whatsapp, password, role)
        VALUES (?, ?, ?, ?, 'admin')
    ");
    $stmt->bind_param("ssss", $nama, $username, $whatsapp, $hash);
    $aksi = "dibuat";
}

if (!$stmt->execute()) {
    fwrite(STDERR, "Gagal: " . $stmt->error . "\n");
    exit(1);
}

echo "Admin '{$username}' berhasil {$aksi}.\n";
echo "Password TIDAK ditampilkan di sini — simpan sendiri.\n";

$stmt->close();
$conn->close();
