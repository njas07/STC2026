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

/* =========================
   CEK LOGIN
========================= */

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Silakan login terlebih dahulu."
    ]);
    exit;
}

$user_id = $_SESSION["user_id"];

/* =========================
   DATA
========================= */

$registration_id = $_POST["registration_id"] ?? null;
$metode_pembayaran = $_POST["metode_pembayaran"] ?? null;
$jumlah_bayar = $_POST["jumlah_bayar"] ?? null;
$tanggal_pembayaran = $_POST["tanggal_pembayaran"] ?? null;

/* =========================
   VALIDASI
========================= */

if (
    !$registration_id ||
    !$metode_pembayaran ||
    !$jumlah_bayar ||
    !$tanggal_pembayaran
) {
    echo json_encode([
        "success" => false,
        "message" => "Data pembayaran belum lengkap."
    ]);
    exit;
}

/* =========================
   CEK REGISTRATION
========================= */

$stmt = $conn->prepare("
    SELECT id, user_id
    FROM registrations
    WHERE id = ?
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

/* Pastikan pendaftaran milik user yang login */

if ($registration["user_id"] != $user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Anda tidak memiliki akses ke pendaftaran ini."
    ]);
    exit;
}

/* =========================
   CEK FILE
========================= */

if (!isset($_FILES["bukti_pembayaran"])) {
    echo json_encode([
        "success" => false,
        "message" => "Bukti pembayaran belum dipilih."
    ]);
    exit;
}

$file = $_FILES["bukti_pembayaran"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "success" => false,
        "message" => "Upload bukti pembayaran gagal."
    ]);
    exit;
}

/* Maksimal 2 MB */

$max_size = 2 * 1024 * 1024;

if ($file["size"] > $max_size) {
    echo json_encode([
        "success" => false,
        "message" => "Ukuran bukti pembayaran maksimal 2MB."
    ]);
    exit;
}

/* =========================
   VALIDASI FORMAT
========================= */

$allowed_extensions = [
    "jpg",
    "jpeg",
    "png",
    "webp"
];

$extension = strtolower(
    pathinfo($file["name"], PATHINFO_EXTENSION)
);

if (!in_array($extension, $allowed_extensions)) {
    echo json_encode([
        "success" => false,
        "message" => "Format harus JPG, PNG, atau WEBP."
    ]);
    exit;
}

/* =========================
   FOLDER UPLOAD
========================= */

/* =========================
   VERIFIKASI ISI FILE
   Ekstensi saja bisa dipalsukan (mis. "virus.php" diganti
   jadi "virus.jpg"). Isi file diperiksa dengan finfo agar
   yang tersimpan benar-benar gambar/PDF.
========================= */

$finfo = new finfo(FILEINFO_MIME_TYPE);
$real_mime = $finfo->file($file["tmp_name"]);

$allowed_mimes = [
    "image/jpeg",
    "image/png",
    "image/webp",
    "application/pdf"
];

if (!in_array($real_mime, $allowed_mimes, true)) {
    echo json_encode([
        "success" => false,
        "message" => "Isi file bukan gambar atau PDF yang sah."
    ]);
    exit;
}

$upload_dir = "../uploads/payments/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

/* =========================
   NAMA FILE
========================= */

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

/* =========================
   SIMPAN FILE
========================= */

if (!move_uploaded_file($file["tmp_name"], $file_path)) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan bukti pembayaran."
    ]);
    exit;
}

/* =========================
   PATH DATABASE
========================= */

$file_path_db =
    "uploads/payments/" . $new_filename;

/* =========================
   STATUS
========================= */

$status_pembayaran = "MENUNGGU_VERIFIKASI";

/* =========================
   SIMPAN DATABASE
========================= */

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

    if (file_exists($file_path)) {
        unlink($file_path);
    }

    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan pembayaran.",
        "error" => $stmt->error
    ]);

    exit;
}

/* =========================
   BERHASIL
========================= */

echo json_encode([
    "success" => true,
    "message" => "Pembayaran berhasil dikirim.",
    "payment_id" => $conn->insert_id,
    "registration_id" => $registration_id,
    "metode_pembayaran" => $metode_pembayaran,
    "jumlah_bayar" => $jumlah_bayar,
    "tanggal_pembayaran" => $tanggal_pembayaran,
    "status_pembayaran" => $status_pembayaran,
    "bukti_pembayaran" => $file_path_db
]);

?>