<?php

session_start();

header("Content-Type: application/json");

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diperbolehkan"
    ]);
    exit;
}

/* ===============================
   CEK LOGIN
================================ */

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Silakan login terlebih dahulu."
    ]);
    exit;
}

$user_id = $_SESSION["user_id"];

/* ===============================
   AMBIL DATA
================================ */

$competition_id = $_POST["competition_id"] ?? null;
$tipe_pendaftaran = $_POST["tipe_pendaftaran"] ?? null;

/* ===============================
   VALIDASI
================================ */

if (!$competition_id || !$tipe_pendaftaran) {
    echo json_encode([
        "success" => false,
        "message" => "Data pendaftaran belum lengkap."
    ]);
    exit;
}

/* ===============================
   CEK LOMBA
================================ */

$stmt = $conn->prepare("
    SELECT id, nama_lomba, tipe, min_anggota, max_anggota
    FROM competitions
    WHERE id = ?
");

$stmt->bind_param("i", $competition_id);
$stmt->execute();

$result = $stmt->get_result();
$competition = $result->fetch_assoc();

if (!$competition) {
    echo json_encode([
        "success" => false,
        "message" => "Lomba tidak ditemukan."
    ]);
    exit;
}

/* ===============================
   PASTIKAN TIPE PENDAFTARAN SESUAI
================================ */

if ($competition["tipe"] !== $tipe_pendaftaran) {
    echo json_encode([
        "success" => false,
        "message" =>
            "Tipe pendaftaran tidak sesuai dengan jenis lomba. " .
            "Lomba ini harus didaftarkan sebagai " .
            $competition["tipe"] . "."
    ]);
    exit;
}

/* ===============================
   BUAT KODE PENDAFTARAN
================================ */

$kode_pendaftaran =
    "STC-" .
    strtoupper(substr($competition["nama_lomba"], 0, 3)) .
    "-" .
    date("YmdHis");

/* ===============================
   SIMPAN PENDAFTARAN
================================ */

$status = "MENUNGGU_VERIFIKASI";

$stmt = $conn->prepare("
    INSERT INTO registrations
    (
        user_id,
        competition_id,
        kode_pendaftaran,
        tipe_pendaftaran,
        status
    )
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iisss",
    $user_id,
    $competition_id,
    $kode_pendaftaran,
    $tipe_pendaftaran,
    $status
);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan pendaftaran.",
        "error" => $stmt->error
    ]);
    exit;
}

$registration_id = $conn->insert_id;

/* ===============================
   BERHASIL
================================ */

echo json_encode([
    "success" => true,
    "message" => "Pendaftaran berhasil dibuat.",
    "registration_id" => $registration_id,
    "kode_pendaftaran" => $kode_pendaftaran
]);