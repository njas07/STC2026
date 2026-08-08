<?php
/**
 * STC 2026 - API Login
 * POST: email, password
 * Response: success + user (id, name, email, role) + redirect
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = db_connect();
$data = json_input();

$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    json_response(false, 'Email dan password wajib diisi.', null, 422);
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    json_response(false, 'Email atau password salah.', null, 401);
}

// Redirect berdasarkan role
$redirect = '';
switch ($user['role']) {
    case 'admin':
        $redirect = '../../admin-dashboard.html';
        break;
    case 'panitia':
        $redirect = '../../admin-dashboard.html';
        break;
    default:
        $redirect = '../../dashboard.html';
}

json_response(true, 'Login berhasil.', [
    'id'       => $user['id'],
    'name'     => $user['name'],
    'email'    => $user['email'],
    'role'     => $user['role'],
    'redirect' => $redirect,
]);
