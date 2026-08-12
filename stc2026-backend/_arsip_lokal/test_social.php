<?php

session_start();

require_once "config/database.php";

$_SESSION["user_id"] = 1;

$registration_id = 1;
$jenis_instagram = "stc";

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
    die("Registration tidak ditemukan.");
}

if ($registration["user_id"] != $_SESSION["user_id"]) {
    die("Registration bukan milik user ini.");
}

/* =========================
   FILE TEST
========================= */

$source_file = __DIR__ . "/test-kartu-pelajar.jpg";

if (!file_exists($source_file)) {
    die("File test-kartu-pelajar.jpg tidak ditemukan.");
}

/* =========================
   CEK UKURAN
========================= */

$file_size = filesize($source_file);

if ($file_size > 2 * 1024 * 1024) {
    die("File terlalu besar. Maksimal 2MB.");
}

/* =========================
   CEK EXTENSION
========================= */

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

/* =========================
   FOLDER
========================= */

$upload_dir = "uploads/social/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* =========================
   NAMA FILE
========================= */

$new_filename =
    "REG-" .
    $registration_id .
    "-" .
    $jenis_instagram .
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
   SIMPAN DATABASE
========================= */

$file_path_db = "uploads/social/" . $new_filename;

$stmt = $conn->prepare("
    INSERT INTO social_proofs
    (
        registration_id,
        jenis_instagram,
        file_path
    )
    VALUES (?, ?, ?)
");

$stmt->bind_param(
    "iss",
    $registration_id,
    $jenis_instagram,
    $file_path_db
);

if (!$stmt->execute()) {

    if (file_exists($file_path)) {
        unlink($file_path);
    }

    die("Gagal menyimpan database: " . $stmt->error);
}

echo "<h2>Bukti Instagram berhasil disimpan!</h2>";

echo "Social Proof ID: " . $conn->insert_id . "<br>";
echo "Registration ID: " . $registration_id . "<br>";
echo "Jenis Instagram: " . $jenis_instagram . "<br>";
echo "Path: " . $file_path_db;

?>