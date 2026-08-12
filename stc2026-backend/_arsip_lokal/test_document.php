<?php

session_start();

require_once "config/database.php";

/* Simulasi user yang sudah login */
$_SESSION["user_id"] = 1;

$registration_id = 1;
$jenis_dokumen = "kartu_pelajar";

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
    die("Registration ID 1 tidak ditemukan.");
}

if ($registration["user_id"] != $_SESSION["user_id"]) {
    die("Registration bukan milik user ini.");
}

/* =========================
   FILE TEST
========================= */

/*
   Ganti path ini dengan lokasi
   file gambar/PDF yang mau dites.
*/

$source_file = __DIR__ . "/test-kartu-pelajar.jpg";

if (!file_exists($source_file)) {
    die(
        "File test tidak ditemukan.<br><br>" .
        "Letakkan file bernama:<br>" .
        "<b>test-kartu-pelajar.jpg</b><br>" .
        "di folder:<br>" .
        "C:/xampp/htdocs/stc2026-backend/"
    );
}

/* =========================
   CEK FILE
========================= */

$file_size = filesize($source_file);
$max_size = 2 * 1024 * 1024;

if ($file_size > $max_size) {
    die("Ukuran file terlalu besar. Maksimal 2MB.");
}

$extension = strtolower(
    pathinfo($source_file, PATHINFO_EXTENSION)
);

$allowed_extensions = [
    "jpg",
    "jpeg",
    "png",
    "webp",
    "pdf"
];

if (!in_array($extension, $allowed_extensions)) {
    die("Format file tidak diperbolehkan.");
}

/* =========================
   FOLDER UPLOAD
========================= */

$upload_dir = "uploads/documents/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* =========================
   NAMA FILE
========================= */

$nama_file_asli = basename($source_file);

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
   COPY FILE
========================= */

if (!copy($source_file, $file_path)) {
    die("Gagal menyalin file.");
}

/* =========================
   DATA DATABASE
========================= */

$nama_file = $new_filename;
$file_path_db = "uploads/documents/" . $new_filename;

$mime_type = mime_content_type($source_file);

if (!$mime_type) {
    $mime_type = "application/octet-stream";
}

$ukuran_file = $file_size;

/* =========================
   SIMPAN DATABASE
========================= */

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

    if (file_exists($file_path)) {
        unlink($file_path);
    }

    die(
        "Gagal menyimpan database:<br>" .
        $stmt->error
    );
}

/* =========================
   BERHASIL
========================= */

echo "<h2>Upload dokumen berhasil!</h2>";

echo "Document ID: " . $conn->insert_id . "<br>";
echo "Registration ID: " . $registration_id . "<br>";
echo "Jenis dokumen: " . $jenis_dokumen . "<br>";
echo "Nama file asli: " . $nama_file_asli . "<br>";
echo "Nama file baru: " . $nama_file . "<br>";
echo "Ukuran: " . $ukuran_file . " bytes<br>";
echo "MIME: " . $mime_type . "<br>";
echo "Path: " . $file_path_db;

?>