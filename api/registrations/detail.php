<?php
/**
 * STC 2026 - API Detail Registration
 * GET: ?id= OR ?code=
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = db_connect();

$id   = $_GET['id'] ?? null;
$code = $_GET['code'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
    $stmt->execute([(int) $id]);
} elseif ($code) {
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE code = ?");
    $stmt->execute([$code]);
} else {
    json_response(false, 'Parameter id atau code wajib diisi.', null, 422);
}

$row = $stmt->fetch();
if (!$row) {
    json_response(false, 'Data tidak ditemukan.', null, 404);
}

$row['data'] = $row['data'] ? json_decode($row['data'], true) : null;

json_response(true, 'Berhasil.', $row);
