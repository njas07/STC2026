<?php

session_start();

header("Content-Type: application/json");

require_once "../config/database.php";

/* ===============================
   CEK METHOD
================================ */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diperbolehkan"
    ]);
    exit;
}

/* ===============================
   CEK LOGIN ADMIN
================================ */

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Silakan login terlebih dahulu."
    ]);
    exit;
}

/* ===============================
   CEK ROLE ADMIN
================================ */

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT id, nama_lengkap, role
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user["role"] !== "admin") {
    echo json_encode([
        "success" => false,
        "message" => "Akses hanya untuk admin."
    ]);
    exit;
}

/* ===============================
   AMBIL DATA
================================ */

$payment_id = $_POST["payment_id"] ?? null;
$status = $_POST["status"] ?? null;

/* ===============================
   VALIDASI
================================ */

$allowed_status = [
    "MENUNGGU_VERIFIKASI",
    "VALID",
    "TIDAK_VALID"
];

if (!$payment_id || !$status) {
    echo json_encode([
        "success" => false,
        "message" => "Payment ID dan status wajib diisi."
    ]);
    exit;
}

if (!in_array($status, $allowed_status)) {
    echo json_encode([
        "success" => false,
        "message" => "Status pembayaran tidak valid."
    ]);
    exit;
}

/* ===============================
   CEK PEMBAYARAN
================================ */

$stmt = $conn->prepare("
    SELECT id, registration_id
    FROM payments
    WHERE id = ?
");

$stmt->bind_param("i", $payment_id);
$stmt->execute();

$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    echo json_encode([
        "success" => false,
        "message" => "Data pembayaran tidak ditemukan."
    ]);
    exit;
}

/* ===============================
   UPDATE STATUS PEMBAYARAN
================================ */

$stmt = $conn->prepare("
    UPDATE payments
    SET status_pembayaran = ?
    WHERE id = ?
");

$stmt->bind_param(
    "si",
    $status,
    $payment_id
);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengubah status pembayaran.",
        "error" => $stmt->error
    ]);
    exit;
}

/* ===============================
   BERHASIL
================================ */

echo json_encode([
    "success" => true,
    "message" => "Status pembayaran berhasil diperbarui.",
    "payment_id" => $payment_id,
    "registration_id" => $payment["registration_id"],
    "status_pembayaran" => $status
]);