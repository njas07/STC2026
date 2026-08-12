<?php

session_start();

require_once "config/database.php";

/* Simulasi user yang sudah login */
$_SESSION["user_id"] = 1;

$registration_id = 1;

$nama_lengkap = "Test Peserta Web Design";
$nis_nisn = "123456789";
$asal_instansi_sekolah = "SMK TI STC 2026";
$kelas = "X PPLG";
$email = "test@stc2026.com";
$no_whatsapp = "081234567890";

$url = "individual/create.php";

/*
 * Untuk sementara kita tes langsung melalui database
 * agar tidak terkena masalah session antar-request.
 */

$user_id = $_SESSION["user_id"];

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
    die("Registration tidak ditemukan.");
}

if ($registration["user_id"] != $user_id) {
    die("Registration bukan milik user ini.");
}

if ($registration["tipe_pendaftaran"] !== "individu") {
    die("Tipe pendaftaran bukan individu.");
}

/* Simpan data */
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

if ($stmt->execute()) {

    echo "<h2>Data peserta individu berhasil disimpan!</h2>";

    echo "ID: " . $conn->insert_id . "<br>";
    echo "Registration ID: " . $registration_id . "<br>";
    echo "Nama: " . $nama_lengkap . "<br>";
    echo "NIS/NISN: " . $nis_nisn . "<br>";
    echo "Sekolah: " . $asal_instansi_sekolah . "<br>";
    echo "Kelas: " . $kelas . "<br>";
    echo "Email: " . $email . "<br>";
    echo "WhatsApp: " . $no_whatsapp;

} else {

    echo "Gagal menyimpan data: " . $stmt->error;
}

?>