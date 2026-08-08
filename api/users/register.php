<?php
/**
 * STC 2026 - API Register Peserta
 * POST: name, email, phone, password, confirm_password
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = db_connect();
$data = json_input();

$name      = trim($data['name'] ?? '');
$email     = trim($data['email'] ?? '');
$phone     = trim($data['phone'] ?? '');
$password  = $data['password'] ?? '';
$confirm   = $data['confirm_password'] ?? '';

// Validasi wajib
if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    json_response(false, 'Semua field wajib diisi.', null, 422);
}

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Format email tidak valid.', null, 422);
}

// Password minimal 6 karakter
if (strlen($password) < 6) {
    json_response(false, 'Password minimal 6 karakter.', null, 422);
}

// Konfirmasi password
if ($password !== $confirm) {
    json_response(false, 'Konfirmasi password tidak sama.', null, 422);
}

// Cek email sudah terdaftar
$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    json_response(false, 'Email sudah terdaftar. Silakan login.', null, 409);
}

// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'peserta')");
$stmt->execute([$name, $email, $phone, $hash]);

json_response(true, 'Pendaftaran akun berhasil. Silakan login.', ['id' => $pdo->lastInsertId()]);
