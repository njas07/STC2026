<?php

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diperbolehkan"
    ]);
    exit;
}

header("Content-Type: application/json");

$nama_lengkap = trim($_POST["nama_lengkap"] ?? "");
$email = trim($_POST["email"] ?? "");
$no_whatsapp = trim($_POST["no_whatsapp"] ?? "");
$password = $_POST["password"] ?? "";
$konfirmasi_password = $_POST["konfirmasi_password"] ?? "";

// Validasi
if (
    empty($nama_lengkap) ||
    empty($email) ||
    empty($no_whatsapp) ||
    empty($password) ||
    empty($konfirmasi_password)
) {
    echo json_encode([
        "success" => false,
        "message" => "Semua field wajib diisi."
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

if (strlen($password) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Password minimal 6 karakter."
    ]);
    exit;
}

if ($password !== $konfirmasi_password) {
    echo json_encode([
        "success" => false,
        "message" => "Konfirmasi password tidak cocok."
    ]);
    exit;
}

// Cek email sudah digunakan atau belum
$check = $conn->prepare(
    "SELECT id FROM users WHERE email = ?"
);

$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email sudah terdaftar."
    ]);
    exit;
}

// Hash password
$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

// Simpan user
$stmt = $conn->prepare(
    "INSERT INTO users
    (nama_lengkap, email, no_whatsapp, password, role)
    VALUES (?, ?, ?, ?, 'peserta')"
);

$stmt->bind_param(
    "ssss",
    $nama_lengkap,
    $email,
    $no_whatsapp,
    $password_hash
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Akun STC 2026 berhasil dibuat.",
        "user_id" => $stmt->insert_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Gagal membuat akun."
    ]);
}

$stmt->close();
$check->close();
$conn->close();

?>