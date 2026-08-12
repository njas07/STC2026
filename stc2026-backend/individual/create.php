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
$nama_lengkap = $_POST["nama_lengkap"] ?? null;
$nis_nisn = $_POST["nis_nisn"] ?? null;
$asal_instansi_sekolah = $_POST["asal_instansi_sekolah"] ?? null;
$kelas = $_POST["kelas"] ?? null;
$email = $_POST["email"] ?? null;
$no_whatsapp = $_POST["no_whatsapp"] ?? null;

if (
    !$registration_id ||
    !$nama_lengkap ||
    !$nis_nisn ||
    !$asal_instansi_sekolah ||
    !$kelas ||
    !$email ||
    !$no_whatsapp
) {
    echo json_encode([
        "success" => false,
        "message" => "Data peserta belum lengkap."
    ]);
    exit;
}

/* Cek registration */

$stmt = $conn->prepare("
    SELECT id, user_id, tipe_pendaftaran
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
        "message" => "Data pendaftaran tidak ditemukan."
    ]);
    exit;
}

/* Pastikan pendaftaran milik user */

if ($registration["user_id"] != $user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Anda tidak memiliki akses ke pendaftaran ini."
    ]);
    exit;
}

/* Pastikan tipe individu */

if ($registration["tipe_pendaftaran"] !== "individu") {
    echo json_encode([
        "success" => false,
        "message" => "Pendaftaran ini bukan pendaftaran individu."
    ]);
    exit;
}

/* Simpan data peserta */

$stmt = $conn->prepare("
    INSERT INTO individual_registrations
    (
        registration_id,
        nama_lengkap,
        nis_nisn,
        asal_instansi_sekolah,
        kelas,
        email,
        no_whatsapp
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "issssss",
    $registration_id,
    $nama_lengkap,
    $nis_nisn,
    $asal_instansi_sekolah,
    $kelas,
    $email,
    $no_whatsapp
);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan data peserta.",
        "error" => $stmt->error
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Data peserta berhasil disimpan.",
    "individual_registration_id" => $conn->insert_id
]);

?>