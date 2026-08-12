<?php

session_start();

header("Content-Type: application/json");

require_once "../config/database.php";

/* ===============================
   CEK LOGIN
================================ */

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Silakan login terlebih dahulu."
    ]);
    exit;
}

$user_id = $_SESSION["user_id"];

/* ===============================
   TUTUP SESSION SECEPATNYA
================================ */

session_write_close();

/* ===============================
   CEK ADMIN
================================ */

$stmt = $conn->prepare("
    SELECT id, nama_lengkap, email, role
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$stmt->close();

if (!$admin || $admin["role"] !== "admin") {
    echo json_encode([
        "success" => false,
        "message" => "Akses hanya untuk admin."
    ]);
    exit;
}

/* ===============================
   AMBIL PENDAFTARAN
================================ */

$sql = "
    SELECT
        r.id AS registration_id,
        r.kode_pendaftaran,
        r.tipe_pendaftaran,
        r.status,
        r.created_at,

        u.id AS user_id,
        u.nama_lengkap AS nama_akun,
        u.email AS email_akun,
        u.no_whatsapp AS whatsapp_akun,

        c.id AS competition_id,
        c.nama_lomba,
        c.tipe AS tipe_lomba,

        ir.nama_lengkap AS nama_peserta,
        ir.nis_nisn,
        ir.asal_instansi_sekolah,
        ir.kelas,
        ir.email AS email_peserta,
        ir.no_whatsapp AS whatsapp_peserta,

        tr.nama_tim,
        tr.nama_sekolah AS tim_sekolah,
        tr.email AS tim_email,
        tr.no_whatsapp AS tim_whatsapp

    FROM registrations r

    INNER JOIN users u
        ON r.user_id = u.id

    INNER JOIN competitions c
        ON r.competition_id = c.id

    LEFT JOIN individual_registrations ir
        ON r.id = ir.registration_id

    LEFT JOIN team_registrations tr
        ON r.id = tr.registration_id

    ORDER BY r.id DESC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengambil data pendaftaran.",
        "error" => $conn->error
    ]);
    exit;
}

/* ===============================
   FORMAT DATA
================================ */

$data = [];

while ($row = $result->fetch_assoc()) {

    $data[] = [
        "registration" => [
            "id" => $row["registration_id"],
            "kode_pendaftaran" => $row["kode_pendaftaran"],
            "tipe_pendaftaran" => $row["tipe_pendaftaran"],
            "status" => $row["status"],
            "created_at" => $row["created_at"]
        ],

        "akun" => [
            "user_id" => $row["user_id"],
            "nama_lengkap" => $row["nama_akun"],
            "email" => $row["email_akun"],
            "no_whatsapp" => $row["whatsapp_akun"]
        ],

        "lomba" => [
            "competition_id" => $row["competition_id"],
            "nama_lomba" => $row["nama_lomba"],
            "tipe" => $row["tipe_lomba"]
        ],

        "peserta" => [
            "nama_lengkap" => $row["nama_peserta"],
            "nis_nisn" => $row["nis_nisn"],
            "asal_instansi_sekolah" => $row["asal_instansi_sekolah"],
            "kelas" => $row["kelas"],
            "email" => $row["email_peserta"],
            "no_whatsapp" => $row["whatsapp_peserta"]
        ],

        "tim" => [
            "nama_tim" => $row["nama_tim"],
            "nama_sekolah" => $row["tim_sekolah"],
            "email" => $row["tim_email"],
            "no_whatsapp" => $row["tim_whatsapp"]
        ]
    ];
}

/* ===============================
   RESPONSE
================================ */

echo json_encode([
    "success" => true,
    "total" => count($data),
    "data" => $data
], JSON_PRETTY_PRINT);

$conn->close();

?>