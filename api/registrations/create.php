<?php
/**
 * STC 2026 - API Create Registration (multipart/form-data)
 * POST: user_id, competition, name, student_id, school_name, school_class,
 *       email, phone, data(JSON), student_card(file), instagram_school,
 *       instagram_school_proof(file), instagram_stc, instagram_stc_proof(file),
 *       payment_method, amount, transaction_date, payment_proof(file)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = db_connect();

$user_id      = (int) ($_POST['user_id'] ?? 0);
$competition  = trim($_POST['competition'] ?? '');
$name         = trim($_POST['name'] ?? '');
$email        = trim($_POST['email'] ?? '');

if (!$user_id || $competition === '' || $name === '' || $email === '') {
    json_response(false, 'Data wajib belum lengkap.', null, 422);
}

// Cek user
$u = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$u->execute([$user_id]);
if (!$u->fetch()) {
    json_response(false, 'User tidak ditemukan.', null, 404);
}

// Upload files
$student_card          = upload_file('student_card');
$instagram_school_proof = upload_file('instagram_school_proof');
$instagram_stc_proof    = upload_file('instagram_stc_proof');
$payment_proof          = upload_file('payment_proof');

foreach (['student_card', 'instagram_school_proof', 'instagram_stc_proof', 'payment_proof'] as $f) {
    if (isset(${$f}) && is_array(${$f})) {
        json_response(false, ${$f}['error'], null, 400);
    }
}

// Data khusus lomba (JSON string dari client) atau array
$extra = [];
if (!empty($_POST['data'])) {
    $decoded = json_decode($_POST['data'], true);
    if (is_array($decoded)) $extra = $decoded;
}

// Generate unique code
$code = generate_code($pdo);

$stmt = $pdo->prepare("
    INSERT INTO registrations (
        code, user_id, competition, name, student_id, school_name, school_class,
        email, phone, data, student_card, instagram_school, instagram_school_proof,
        instagram_stc, instagram_stc_proof, payment_method, amount,
        transaction_date, payment_proof, status
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING'
    )
");

$stmt->execute([
    $code,
    $user_id,
    $competition,
    $name,
    $_POST['student_id'] ?? null,
    $_POST['school_name'] ?? null,
    $_POST['school_class'] ?? null,
    $email,
    $_POST['phone'] ?? null,
    json_encode($extra),
    $student_card,
    $_POST['instagram_school'] ?? null,
    $instagram_school_proof,
    $_POST['instagram_stc'] ?? null,
    $instagram_stc_proof,
    $_POST['payment_method'] ?? null,
    $_POST['amount'] ?? null,
    $_POST['transaction_date'] ?? null,
    $payment_proof,
]);

json_response(true, 'Pendaftaran berhasil.', [
    'code'   => $code,
    'id'     => $pdo->lastInsertId(),
    'status' => 'PENDING',
], 201);
