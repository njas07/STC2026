<?php

session_start();

require_once "config/database.php";

$_SESSION["user_id"] = 1;

$registration_id = 1;
$metode_pembayaran = "Transfer Bank";
$jumlah_bayar = 100000;
$tanggal_pembayaran = date("Y-m-d");

$source_file = __DIR__ . "/test-kartu-pelajar.jpg";

if (!file_exists($source_file)) {
    die("File test-kartu-pelajar.jpg tidak ditemukan.");
}

$file_size = filesize($source_file);

if ($file_size > 2 * 1024 * 1024) {
    die("File terlalu besar.");
}

$extension = strtolower(
    pathinfo($source_file, PATHINFO_EXTENSION)
);

$allowed_extensions = [
    "jpg",
    "jpeg",
    "png",
    "webp"
];

if (!in_array($extension, $allowed_extensions)) {
    die("Format file tidak diperbolehkan.");
}

$upload_dir = "uploads/payments/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$new_filename =
    "REG-" .
    $registration_id .
    "-PAY-" .
    time() .
    "-" .
    uniqid() .
    "." .
    $extension;

$file_path = $upload_dir . $new_filename;

if (!copy($source_file, $file_path)) {
    die("Gagal menyalin file.");
}

$file_path_db = "uploads/payments/" . $new_filename;

$status_pembayaran = "MENUNGGU_VERIFIKASI";

$stmt = $conn->prepare("
    INSERT INTO payments
    (
        registration_id,
        metode_pembayaran,
        jumlah_bayar,
        tanggal_pembayaran,
        bukti_pembayaran,
        status_pembayaran
    )
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isdsss",
    $registration_id,
    $metode_pembayaran,
    $jumlah_bayar,
    $tanggal_pembayaran,
    $file_path_db,
    $status_pembayaran
);

if (!$stmt->execute()) {
    die("Gagal menyimpan pembayaran: " . $stmt->error);
}

echo "<h2>Pembayaran berhasil disimpan!</h2>";

echo "Payment ID: " . $conn->insert_id . "<br>";
echo "Registration ID: " . $registration_id . "<br>";
echo "Metode: " . $metode_pembayaran . "<br>";
echo "Jumlah: Rp " . number_format($jumlah_bayar, 0, ',', '.') . "<br>";
echo "Tanggal: " . $tanggal_pembayaran . "<br>";
echo "Status: " . $status_pembayaran . "<br>";
echo "Bukti: " . $file_path_db;

?>