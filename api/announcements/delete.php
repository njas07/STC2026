<?php
/**
 * STC 2026 - API Delete Announcement
 * POST: id
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = db_connect();
$data = json_input();

$id = (int) ($data['id'] ?? 0);
if (!$id) {
    json_response(false, 'ID wajib diisi.', null, 422);
}

$stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
$stmt->execute([$id]);

json_response(true, 'Pengumuman dihapus.', ['id' => $id]);
