<?php
/**
 * STC 2026 - API Update Announcement
 * POST: id, title, content, status
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo  = db_connect();
$data = json_input();

$id      = (int) ($data['id'] ?? 0);
$title   = trim($data['title'] ?? '');
$content = trim($data['content'] ?? '');
$status  = ($data['status'] ?? 'draft') === 'published' ? 'published' : 'draft';

if (!$id || $title === '') {
    json_response(false, 'Data tidak valid.', null, 422);
}

$stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, status = ? WHERE id = ?");
$stmt->execute([$title, $content, $status, $id]);

json_response(true, 'Pengumuman diperbarui.');
