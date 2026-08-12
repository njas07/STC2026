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

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Silakan login terlebih dahulu."
    ]);
    exit;
}

$user_id = $_SESSION["user_id"];

$registration_id = $_POST["registration_id"] ?? null;
$jenis_dokumen = $_POST["jenis_dokumen"] ?? null;

if (!$registration_id || !$jenis_dokumen) {
    echo json_encode([
        "success" => false,
        "message" => "Data dokumen belum lengkap."
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

if (!isset($_FILES["file"])) {
    echo json_encode([
        "success" => false,
        "message" => "File belum dipilih."
    ]);
    exit;
}

$file = $_FILES["file"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "success" => false,
        "message" => "Upload file gagal."
    ]);
    exit;
}

/* Maksimal 2 MB */

$max_size = 2 * 1024 * 1024;

if ($file["size"] > $max_size) {
    echo json_encode([
        "success" => false,
        "message" => "Ukuran file maksimal 2MB."
    ]);
    exit;
}

/* =========================
   CEK EXTENSION
========================= */

$allowed_extensions = [
    "jpg",
    "jpeg",
    "png",
    "webp",
    "pdf"
];

$extension = strtolower(
    pathinfo($file["name"], PATHINFO_EXTENSION)
);

if (!in_array($extension, $allowed_extensions)) {
    echo json_encode([
        "success" => false,
        "message" => "Format file tidak diperbolehkan."
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

$upload_dir = "../uploads/documents/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

/* =========================
   NAMA FILE BARU
========================= */

$new_filename =
    "REG-" .
    $registration_id .
    "-" .
    time() .
    "-" .
    uniqid() .
    "." .
    $extension;

$file_path = $upload_dir . $new_filename;

/* =========================
   PINDAHKAN FILE
========================= */

if (!move_uploaded_file($file["tmp_name"], $file_path)) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan file."
    ]);
    exit;
}

/* =========================
   SIMPAN DATABASE
========================= */

$nama_file_asli = $file["name"];
$nama_file = $new_filename;
$file_path_db = "uploads/documents/" . $new_filename;
$mime_type = $real_mime;
$ukuran_file = $file["size"];

/*
 * Untuk pendaftaran individu,
 * team_member_id = NULL
 */

$stmt = $conn->prepare("
    INSERT INTO registration_documents
    (
        registration_id,
        team_member_id,
        jenis_dokumen,
        nama_file_asli,
        nama_file,
        file_path,
        mime_type,
        ukuran_file
    )
    VALUES (?, NULL, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isssssi",
    $registration_id,
    $jenis_dokumen,
    $nama_file_asli,
    $nama_file,
    $file_path_db,
    $mime_type,
    $ukuran_file
);

if (!$stmt->execute()) {

    /* Hapus file jika database gagal */
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan data dokumen.",
        "error" => $stmt->error
    ]);

    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Dokumen berhasil diupload.",
    "document_id" => $conn->insert_id,
    "jenis_dokumen" => $jenis_dokumen,
    "nama_file" => $nama_file
]);

?>