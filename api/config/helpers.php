<?php
/**
 * STC 2026 - Helper Functions
 */

function json_response($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ]);
    exit;
}

function json_input() {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?: $_POST;
}

function generate_code($pdo) {
    // STC26-00001
    $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM registrations");
    $count = (int) $stmt->fetch()['cnt'] + 1;
    $code = 'STC26-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    // Pastikan unik
    while (true) {
        $check = $pdo->prepare("SELECT id FROM registrations WHERE code = ?");
        $check->execute([$code]);
        if (!$check->fetch()) break;
        $count++;
        $code = 'STC26-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
    return $code;
}

function upload_file($field, $dir = 'uploads') {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload gagal. Kode error: ' . $_FILES[$field]['error']];
    }

    $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'];
    if (!in_array($_FILES[$field]['type'], $allowed)) {
        return ['error' => 'Format file tidak diizinkan. Gunakan JPG/PNG/PDF.'];
    }

    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($_FILES[$field]['size'] > $maxSize) {
        return ['error' => 'Ukuran file maksimal 2MB.'];
    }

    $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
    $filename = $field . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $targetDir = __DIR__ . '/../../' . $dir . '/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetDir . $filename)) {
        return $dir . '/' . $filename;
    }
    return ['error' => 'Gagal menyimpan file.'];
}
