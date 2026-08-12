<?php
/* =========================================================
   STC 2026 — SIAPA YANG SEDANG LOGIN

   Dipakai halaman dashboard untuk memastikan identitas
   pengguna berasal dari session di server + tabel users,
   bukan dari localStorage yang bisa diubah siapa saja
   lewat console browser.

   GET  auth/me.php          -> user yang sedang login
   GET  auth/me.php?admin=1  -> gagal 403 kalau bukan admin
   ========================================================= */

require_once __DIR__ . "/../config/guard.php";

/* CORS: hanya origin yang dikenal, dan wajib kirim cookie */
$origin = $_SERVER["HTTP_ORIGIN"] ?? "";

$allowed_origins = [
    "http://localhost",
    "http://localhost:5500",
    "http://localhost:5501",
    "http://localhost:8770",
    "http://127.0.0.1",
    "http://127.0.0.1:5500",
    "http://127.0.0.1:5501"
];

if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Credentials: true");
    header("Vary: Origin");
}

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

$butuh_admin = ($_GET["admin"] ?? "") === "1";

$user = $butuh_admin ? require_admin() : require_login();

stc_json([
    "success" => true,
    "user" => [
        "id" => (int) $user["id"],
        "name" => $user["nama_lengkap"],
        "email" => $user["email"],
        "role" => $user["role"]
    ]
]);
