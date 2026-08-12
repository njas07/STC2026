<?php

session_start();

header("Content-Type: application/json");

require_once "../config/database.php";

/* ================================
   CEK LOGIN
================================ */

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Silakan login terlebih dahulu."
    ]);
    exit;
}

/* ================================
   CEK ROLE ADMIN
================================ */

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT id, role
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin || $admin["role"] !== "admin") {
    echo json_encode([
        "success" => false,
        "message" => "Akses hanya untuk admin."
    ]);
    exit;
}

/* ================================
   AMBIL DATA
================================ */

$registration_id = $_POST["registration_id"] ?? null;
$status = $_POST["status"] ?? null;

if (!$registration_id || !$status) {
    echo json_encode([
        "success" => false,
        "message" => "Registration ID dan status wajib diisi."
    ]);
    exit;
}

/* ================================
   STATUS YANG DIIZINKAN
================================ */

$allowed_status = [
    "DIVERIFIKASI",
    "DITOLAK"
];

if (!in_array($status, $allowed_status, true)) {
    echo json_encode([
        "success" => false,
        "message" => "Status tidak valid."
    ]);
    exit;
}

/* ================================
   CEK PENDAFTARAN
================================ */

$stmt = $conn->prepare("
    SELECT id, status
    FROM registrations
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();
$registration = $result->fetch_assoc();

if (!$registration) {
    echo json_encode([
        "success" => false,
        "message" => "Pendaftaran tidak ditemukan."
    ]);
    exit;
}

/* ================================
   UPDATE STATUS
================================ */

$stmt = $conn->prepare("
    UPDATE registrations
    SET status = ?
    WHERE id = ?
");

$stmt->bind_param("si", $status, $registration_id);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui status.",
        "error" => $stmt->error
    ]);
    exit;
}

/* ================================
   RESPONSE
================================ */

echo json_encode([
    "success" => true,
    "message" => "Status pendaftaran berhasil diperbarui.",
    "registration_id" => $registration_id,
    "status" => $status
]);

?>