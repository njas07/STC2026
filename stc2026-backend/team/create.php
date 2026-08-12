<?php

session_start();

header("Content-Type: application/json");

require_once "../config/database.php";

/* =================================
   ONLY POST
================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diperbolehkan. Gunakan POST."
    ]);
    exit;
}

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

$user_id = (int) $_SESSION["user_id"];

/* =================================
   AMBIL DATA TIM
================================= */

$registration_id = $_POST["registration_id"] ?? null;
$nama_tim        = trim($_POST["nama_tim"] ?? "");
$nama_sekolah    = trim($_POST["nama_sekolah"] ?? "");
$email           = trim($_POST["email"] ?? "");
$no_whatsapp     = trim($_POST["no_whatsapp"] ?? "");

/*
 * Frontend dapat mengirim:
 * 1. members sebagai JSON string
 * 2. anggota sebagai JSON string
 * 3. members[] sebagai array PHP
 */
$members_input = $_POST["members"] ?? $_POST["anggota"] ?? null;

if (is_string($members_input)) {
    $decoded = json_decode($members_input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            "success" => false,
            "message" => "Format data anggota tidak valid."
        ]);
        exit;
    }

    $members = $decoded;
} elseif (is_array($members_input)) {
    $members = $members_input;
} else {
    $members = [];
}

/*
 * Dukungan untuk form HTML yang mengirim:
 * nama_lengkap[], nis_nisn[], kelas[]
 */
if (empty($members)
    && isset($_POST["nama_lengkap"])
    && is_array($_POST["nama_lengkap"])
) {
    $names = $_POST["nama_lengkap"];
    $nisns = $_POST["nis_nisn"] ?? [];
    $classes = $_POST["kelas"] ?? [];

    foreach ($names as $i => $name) {
        $members[] = [
            "nama_lengkap" => $name,
            "nis_nisn" => $nisns[$i] ?? "",
            "kelas" => $classes[$i] ?? ""
        ];
    }
}

/* =================================
   VALIDASI DATA TIM
================================= */

if (
    !$registration_id ||
    !$nama_tim ||
    !$nama_sekolah ||
    !$email ||
    !$no_whatsapp
) {
    echo json_encode([
        "success" => false,
        "message" => "Data tim belum lengkap."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Format email tidak valid."
    ]);
    exit;
}

if (!is_array($members)) {
    echo json_encode([
        "success" => false,
        "message" => "Data anggota tim tidak valid."
    ]);
    exit;
}

$registration_id = (int) $registration_id;

/* =================================
   CEK REGISTRATION
================================= */

$stmt = $conn->prepare("
    SELECT
        r.id,
        r.user_id,
        r.competition_id,
        r.tipe_pendaftaran
    FROM registrations r
    WHERE r.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();
$registration = $result->fetch_assoc();
$stmt->close();

if (!$registration) {
    echo json_encode([
        "success" => false,
        "message" => "Data pendaftaran tidak ditemukan."
    ]);
    exit;
}

/* =================================
   PASTIKAN MILIK USER
================================= */

if ((int) $registration["user_id"] !== $user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Anda tidak memiliki akses ke pendaftaran ini."
    ]);
    exit;
}

/* =================================
   PASTIKAN TIPE TIM
================================= */

if ($registration["tipe_pendaftaran"] !== "tim") {
    echo json_encode([
        "success" => false,
        "message" => "Pendaftaran ini bukan pendaftaran tim."
    ]);
    exit;
}

/* =================================
   AMBIL ATURAN LOMBA
================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        nama_lomba,
        tipe,
        min_anggota,
        max_anggota
    FROM competitions
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $registration["competition_id"]);
$stmt->execute();

$result = $stmt->get_result();
$competition = $result->fetch_assoc();
$stmt->close();

if (!$competition) {
    echo json_encode([
        "success" => false,
        "message" => "Lomba tidak ditemukan."
    ]);
    exit;
}

if ($competition["tipe"] !== "tim") {
    echo json_encode([
        "success" => false,
        "message" => "Lomba ini bukan lomba tim."
    ]);
    exit;
}

/* =================================
   VALIDASI JUMLAH ANGGOTA
================================= */

$jumlah_anggota = count($members);
$min_anggota = (int) $competition["min_anggota"];
$max_anggota = (int) $competition["max_anggota"];

if (
    $jumlah_anggota < $min_anggota ||
    $jumlah_anggota > $max_anggota
) {
    echo json_encode([
        "success" => false,
        "message" =>
            "Jumlah anggota untuk " .
            $competition["nama_lomba"] .
            " harus antara " .
            $min_anggota .
            " sampai " .
            $max_anggota .
            " orang.",
        "jumlah_dikirim" => $jumlah_anggota,
        "min_anggota" => $min_anggota,
        "max_anggota" => $max_anggota
    ]);
    exit;
}

/* =================================
   VALIDASI SETIAP ANGGOTA
================================= */

$clean_members = [];

foreach ($members as $index => $member) {

    if (!is_array($member)) {
        echo json_encode([
            "success" => false,
            "message" => "Data anggota ke-" . ($index + 1) . " tidak valid."
        ]);
        exit;
    }

    $nama = trim($member["nama_lengkap"] ?? "");
    $nisn = trim($member["nis_nisn"] ?? "");
    $kelas = trim($member["kelas"] ?? "");

    if (!$nama || !$nisn || !$kelas) {
        echo json_encode([
            "success" => false,
            "message" =>
                "Nama, NIS/NISN, dan kelas anggota ke-" .
                ($index + 1) .
                " wajib diisi."
        ]);
        exit;
    }

    $clean_members[] = [
        "nama_lengkap" => $nama,
        "nis_nisn" => $nisn,
        "kelas" => $kelas
    ];
}

/* =================================
   CEK AGAR BELUM DISIMPAN
================================= */

$stmt = $conn->prepare("
    SELECT id
    FROM team_registrations
    WHERE registration_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();
$existing_team = $result->fetch_assoc();
$stmt->close();

if ($existing_team) {
    echo json_encode([
        "success" => false,
        "message" => "Data tim untuk pendaftaran ini sudah tersimpan."
    ]);
    exit;
}

/* =================================
   SIMPAN SEMUA DALAM TRANSACTION
================================= */

$conn->begin_transaction();

try {

    /* TEAM REGISTRATION */

    $stmt = $conn->prepare("
        INSERT INTO team_registrations
        (
            registration_id,
            nama_tim,
            nama_sekolah,
            email,
            no_whatsapp
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issss",
        $registration_id,
        $nama_tim,
        $nama_sekolah,
        $email,
        $no_whatsapp
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $team_registration_id = $conn->insert_id;
    $stmt->close();

    /* TEAM MEMBERS */

    $stmt = $conn->prepare("
        INSERT INTO team_members
        (
            registration_id,
            nomor_anggota,
            nama_lengkap,
            nis_nisn,
            kelas
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($clean_members as $index => $member) {

        $nomor_anggota = $index + 1;

        $stmt->bind_param(
            "iisss",
            $registration_id,
            $nomor_anggota,
            $member["nama_lengkap"],
            $member["nis_nisn"],
            $member["kelas"]
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }

    $stmt->close();

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Data tim berhasil disimpan.",
        "registration_id" => $registration_id,
        "team_registration_id" => $team_registration_id,
        "lomba" => $competition["nama_lomba"],
        "jumlah_anggota" => $jumlah_anggota
    ]);

} catch (Throwable $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan data tim.",
        "error" => $e->getMessage()
    ]);
}

$conn->close();

?>
