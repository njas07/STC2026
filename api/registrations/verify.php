<?php
/**
 * STC 2026 - API Verifikasi / Tolak Pendaftaran (Admin/Panitia)
 * POST: id, action (verify|reject|paid), reject_note (jika reject/paid)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo   = db_connect();
$data  = json_input();

$id    = (int) ($data['id'] ?? 0);
$action = $data['action'] ?? '';
$note  = trim($data['reject_note'] ?? '');

if (!$id || !in_array($action, ['verify', 'reject', 'paid'])) {
    json_response(false, 'Parameter tidak valid.', null, 422);
}

$check = $pdo->prepare("SELECT id FROM registrations WHERE id = ?");
$check->execute([$id]);
if (!$check->fetch()) {
    json_response(false, 'Data pendaftaran tidak ditemukan.', null, 404);
}

$newStatus = 'PENDING';
if ($action === 'verify') $newStatus = 'VERIFIED';
if ($action === 'reject') $newStatus = 'REJECTED';
if ($action === 'paid')   $newStatus = 'PAID';

$stmt = $pdo->prepare("UPDATE registrations SET status = ?, reject_note = ? WHERE id = ?");
$stmt->execute([$newStatus, $note, $id]);

json_response(true, 'Status diperbarui menjadi ' . $newStatus . '.', ['id' => $id, 'status' => $newStatus]);
