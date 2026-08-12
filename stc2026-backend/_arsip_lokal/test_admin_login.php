<?php

session_start();

require_once "config/database.php";

$email = "admin@stc2026.com";
$password = "admin123";

$stmt = $conn->prepare("
    SELECT id, nama_lengkap, email, password, role
    FROM users
    WHERE email = ?
    LIMIT 1
");

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Admin tidak ditemukan.");
}

if (!password_verify($password, $user["password"])) {
    die("Password admin salah.");
}

if ($user["role"] !== "admin") {
    die("Akun ini bukan admin.");
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["role"] = $user["role"];

echo "<h2>Login admin berhasil!</h2>";

echo "ID Admin: " . $user["id"] . "<br>";
echo "Nama: " . $user["nama_lengkap"] . "<br>";
echo "Email: " . $user["email"] . "<br>";
echo "Role: " . $user["role"];

?>