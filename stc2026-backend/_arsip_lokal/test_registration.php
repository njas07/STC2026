<?php

session_start();

require_once "config/database.php";

$_SESSION["user_id"] = 1;

$user_id = $_SESSION["user_id"];
$competition_id = 1;
$tipe_pendaftaran = "individu";
$status = "MENUNGGU_VERIFIKASI";

/* Cek lomba */
$stmt = $conn->prepare("
    SELECT id, nama_lomba, tipe, min_anggota, max_anggota
    FROM competitions
    WHERE id = ?
");

$stmt->bind_param("i", $competition_id);
$stmt->execute();

$result = $stmt->get_result();
$competition = $result->fetch_assoc();

if (!$competition) {
    die("Lomba dengan ID tersebut tidak ditemukan.");
}

/* Pastikan tipe sesuai */
if ($competition["tipe"] !== $tipe_pendaftaran) {
    die("Tipe pendaftaran tidak sesuai dengan lomba.");
}

/* Buat kode */
$kode_pendaftaran =
    "STC-" .
    strtoupper(substr($competition["nama_lomba"], 0, 3)) .
    "-" .
    date("YmdHis");

/* Simpan registration */
$stmt = $conn->prepare("
    INSERT INTO registrations
    (
        user_id,
        competition_id,
        kode_pendaftaran,
        tipe_pendaftaran,
        status
    )
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iisss",
    $user_id,
    $competition_id,
    $kode_pendaftaran,
    $tipe_pendaftaran,
    $status
);

if ($stmt->execute()) {

    echo "<h3>Pendaftaran berhasil!</h3>";
    echo "Registration ID: " . $conn->insert_id . "<br>";
    echo "Kode Pendaftaran: " . $kode_pendaftaran . "<br>";
    echo "Lomba: " . $competition["nama_lomba"] . "<br>";
    echo "Tipe: " . $competition["tipe"] . "<br>";
    echo "Status: " . $status;

} else {

    echo "Gagal menyimpan: " . $stmt->error;
}

?>