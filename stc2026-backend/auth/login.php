<?php

/* SESSION */
session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "secure" => false,
    "httponly" => true,
    "samesite" => "Lax"
]);

session_start();

/* HEADER */
header("Content-Type: application/json; charset=UTF-8");

$origin = $_SERVER["HTTP_ORIGIN"] ?? "";

$allowed_origins = [
    "http://localhost:5500",
    "http://localhost:5501",
    "http://127.0.0.1:5500",
    "http://127.0.0.1:5501"
];

if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Credentials: true");
    header("Vary: Origin");
}

header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

/* PREFLIGHT */
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

/* CEK METHOD */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diperbolehkan. Gunakan POST."
    ]);
    exit;
}

/* DATABASE + GUARD */
require_once "../config/database.php";
require_once "../config/guard.php";

/* DATA LOGIN */
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Email dan password wajib diisi."
    ]);
    exit;
}

/* BATAS PERCOBAAN
   Menahan serangan tebak password: maksimal 8 percobaan gagal
   per 15 menit untuk kombinasi IP + email yang sama. */

$throttle_key = sha1(
    ($_SERVER["REMOTE_ADDR"] ?? "cli") . "|" . strtolower($email)
);

login_throttle_check($throttle_key);

/* CARI USER */
$stmt = $conn->prepare("
    SELECT
        id,
        nama_lengkap,
        email,
        no_whatsapp,
        password,
        role
    FROM users
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Query login gagal.",
        "error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    /* Tetap jalankan hash palsu supaya lama proses untuk email
       yang ada dan tidak ada sama saja — email terdaftar jadi
       tidak bisa ditebak dari selisih waktu respons. */
    password_verify(
        $password,
        '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30M1MlGsS.a'
    );

    login_throttle_fail($throttle_key);

    echo json_encode([
        "success" => false,
        "message" => "Email atau password salah."
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();

/* CEK PASSWORD */
if (!password_verify($password, $user["password"])) {
    login_throttle_fail($throttle_key);

    echo json_encode([
        "success" => false,
        "message" => "Email atau password salah."
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

/* Login berhasil — hitungan percobaan direset */
login_throttle_reset($throttle_key);

/* SESSION ADMIN / USER
   ID session diganti supaya session lama (yang mungkin sudah
   diketahui penyerang) tidak bisa dipakai — anti session fixation. */
session_regenerate_id(true);

$_SESSION["user_id"] = (int) $user["id"];
$_SESSION["nama_lengkap"] = $user["nama_lengkap"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];

session_write_close();

/* RESPONSE */
echo json_encode([
    "success" => true,
    "message" => "Login berhasil.",
    "user" => [
        "id" => $user["id"],
        "nama_lengkap" => $user["nama_lengkap"],
        "email" => $user["email"],
        "no_whatsapp" => $user["no_whatsapp"],
        "role" => $user["role"]
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();

?>