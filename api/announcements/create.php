<?php
/**
 * STC 2026 - API Create Announcement (multipart/form-data)
 * POST: title, content, status, image(file)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = db_connect();

$title   = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$status  = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';

if ($title === '') {
    json_response(false, 'Judul wajib diisi.', null, 422);
}

$image = upload_file('image');

if (is_array($image)) {
    json_response(false, $image['error'], null, 400);
}

$stmt = $pdo->prepare("INSERT INTO announcements (title, image, content, status) VALUES (?, ?, ?, ?)");
$stmt->execute([$title, $image, $content, $status]);

json_response(true, 'Pengumuman berhasil disimpan.', [
    'id'     => $pdo->lastInsertId(),
    'status' => $status,
], 201);
