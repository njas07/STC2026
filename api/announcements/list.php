<?php
/**
 * STC 2026 - API List Announcements
 * GET: ?all=1 (termasuk draft, untuk admin)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = db_connect();

if (!empty($_GET['all'])) {
    $sql = "SELECT * FROM announcements ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM announcements WHERE status = 'published' ORDER BY created_at DESC";
}

$rows = $pdo->query($sql)->fetchAll();

json_response(true, 'Berhasil.', $rows);
