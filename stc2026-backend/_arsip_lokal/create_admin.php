<?php

require_once "config/database.php";

$nama = "Admin STC 2026";
$email = "admin@stc2026.com";
$whatsapp = "081234567890";
$password = "admin123";
$role = "admin";

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO users
    (nama_lengkap, email, no_whatsapp, password, role)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssss",
    $nama,
    $email,
    $whatsapp,
    $password_hash,
    $role
);

if ($stmt->execute()) {
    echo "Admin berhasil dibuat!<br><br>";
    echo "Email: " . $email . "<br>";
    echo "Password: " . $password;
} else {
    echo "Gagal: " . $stmt->error;
}

?>