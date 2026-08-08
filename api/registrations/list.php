<?php
/**
 * STC 2026 - API List Registrations
 * GET: ?user_id= (untuk peserta) atau semua untuk admin
 *      ?competition= (filter)
 *      ?status= (filter)
 *      ?search= (cari nama/kode)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = db_connect();

$where  = [];
$params = [];

$user_id = $_GET['user_id'] ?? null;
if ($user_id) {
    $where[] = "user_id = ?";
    $params[] = (int) $user_id;
}

if (!empty($_GET['competition'])) {
    $where[] = "competition = ?";
    $params[] = $_GET['competition'];
}

if (!empty($_GET['status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
}

if (!empty($_GET['search'])) {
    $where[] = "(name LIKE ? OR code LIKE ?)";
    $term = '%' . $_GET['search'] . '%';
    $params[] = $term;
    $params[] = $term;
}

$sql = "SELECT * FROM registrations";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Decode JSON data
foreach ($rows as &$r) {
    $r['data'] = $r['data'] ? json_decode($r['data'], true) : null;
}

json_response(true, 'Berhasil mengambil data.', $rows);
