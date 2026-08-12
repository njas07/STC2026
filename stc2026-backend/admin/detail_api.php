<?php

session_start();

header("Content-Type: application/json");

require_once "../config/database.php";

/* =================================
   CEK LOGIN
================================= */

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Silakan login terlebih dahulu."
    ]);
    exit;
}

$user_id = $_SESSION["user_id"];


/* =================================
   CEK ADMIN
================================= */

$stmt = $conn->prepare("
    SELECT id, role
    FROM users
    WHERE id = ?
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


/* =================================
   AMBIL REGISTRATION ID
================================= */

$registration_id = $_GET["id"] ?? null;

if (!$registration_id || !ctype_digit((string)$registration_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Registration ID wajib diisi."
    ]);
    exit;
}

$registration_id = (int) $registration_id;


/* =================================
   DATA PENDAFTARAN
================================= */

$stmt = $conn->prepare("
    SELECT
        r.id,
        r.user_id,
        r.competition_id,
        r.kode_pendaftaran,
        r.tipe_pendaftaran,
        r.status,
        r.created_at,

        u.nama_lengkap AS nama_akun,
        u.email AS email_akun,
        u.no_whatsapp AS whatsapp_akun,

        c.nama_lomba,
        c.tipe AS tipe_lomba

    FROM registrations r

    INNER JOIN users u
        ON r.user_id = u.id

    INNER JOIN competitions c
        ON r.competition_id = c.id

    WHERE r.id = ?
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


/* =================================
   DATA PESERTA INDIVIDU
================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        registration_id,
        nama_lengkap,
        nis_nisn,
        asal_instansi_sekolah,
        kelas,
        email,
        no_whatsapp
    FROM individual_registrations
    WHERE registration_id = ?
");

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();
$individual = $result->fetch_assoc();


/* =================================
   DATA TEAM REGISTRATION
================================= */

$team_registration = null;

$stmt = $conn->prepare("
    SELECT
        id,
        registration_id,
        nama_tim,
        nama_sekolah,
        email,
        no_whatsapp,
        created_at
    FROM team_registrations
    WHERE registration_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();
$team_registration = $result->fetch_assoc();


/* =================================
   DATA TEAM MEMBERS
================================= */

$team_members = [];

$stmt = $conn->prepare("
    SELECT
        id,
        registration_id,
        nomor_anggota,
        nama_lengkap,
        nis_nisn,
        kelas
    FROM team_members
    WHERE registration_id = ?
    ORDER BY nomor_anggota ASC, id ASC
");

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $team_members[] = $row;
}


/* =================================
   DATA DOKUMEN
================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        registration_id,
        team_member_id,
        jenis_dokumen,
        nama_file_asli,
        nama_file,
        file_path,
        mime_type,
        ukuran_file
    FROM registration_documents
    WHERE registration_id = ?
    ORDER BY id ASC
");

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();

$documents = [];

while ($row = $result->fetch_assoc()) {
    $documents[] = $row;
}


/* =================================
   PAYMENT
================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        registration_id,
        metode_pembayaran,
        jumlah_bayar,
        tanggal_pembayaran,
        bukti_pembayaran,
        status_pembayaran,
        created_at
    FROM payments
    WHERE registration_id = ?
    ORDER BY id DESC
    LIMIT 1
");

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();
$payment = $result->fetch_assoc();


/* =================================
   RESPONSE
================================= */

echo json_encode([
    "success" => true,

    "registration" => [
        "id" => $registration["id"],
        "user_id" => $registration["user_id"],
        "competition_id" => $registration["competition_id"],
        "kode_pendaftaran" => $registration["kode_pendaftaran"],
        "tipe_pendaftaran" => $registration["tipe_pendaftaran"],
        "status" => $registration["status"],
        "created_at" => $registration["created_at"]
    ],

    "akun" => [
        "nama_lengkap" => $registration["nama_akun"],
        "email" => $registration["email_akun"],
        "no_whatsapp" => $registration["whatsapp_akun"]
    ],

    "lomba" => [
        "nama_lomba" => $registration["nama_lomba"],
        "tipe" => $registration["tipe_lomba"]
    ],

    /* INDIVIDU */
    "peserta_individu" => $individual,

    /* TIM */
    "team_registration" => $team_registration,
    "team_members" => $team_members,

    /* DOKUMEN */
    "documents" => $documents,

    /* PAYMENT */
    "payment" => $payment

], JSON_PRETTY_PRINT);

?>