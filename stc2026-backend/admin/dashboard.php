<?php

session_start();

require_once "../config/database.php";

/* =================================
   CEK LOGIN ADMIN
================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* =================================
   CEK ROLE ADMIN
================================= */

$stmt = $conn->prepare("
    SELECT id, nama_lengkap, email, role
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin || $admin["role"] !== "admin") {
    die("Akses hanya untuk admin.");
}

/* =================================
   STATISTIK
================================= */

$total = 0;
$menunggu = 0;
$diverifikasi = 0;
$ditolak = 0;

/* Total */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM registrations
");

if ($result) {
    $total = $result->fetch_assoc()["total"];
}

/* Menunggu */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM registrations
    WHERE status = 'MENUNGGU_VERIFIKASI'
");

if ($result) {
    $menunggu = $result->fetch_assoc()["total"];
}

/* Diverifikasi */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM registrations
    WHERE status = 'DIVERIFIKASI'
");

if ($result) {
    $diverifikasi = $result->fetch_assoc()["total"];
}

/* Ditolak */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM registrations
    WHERE status = 'DITOLAK'
");

if ($result) {
    $ditolak = $result->fetch_assoc()["total"];
}

/* =================================
   DATA PENDAFTARAN
================================= */

$sql = "
    SELECT
        r.id,
        r.kode_pendaftaran,
        r.tipe_pendaftaran,
        r.status,
        r.created_at,

        u.nama_lengkap AS akun_nama,

        c.nama_lomba,

        ir.nama_lengkap AS peserta_nama,
        tr.nama_tim AS tim_nama

    FROM registrations r

    INNER JOIN users u
        ON r.user_id = u.id

    INNER JOIN competitions c
        ON r.competition_id = c.id

    LEFT JOIN individual_registrations ir
        ON r.id = ir.registration_id

    LEFT JOIN team_registrations tr
        ON r.id = tr.registration_id

    ORDER BY r.id DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Gagal mengambil data pendaftaran: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - STC 2026</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #1f2937;
        }

        .container {
            width: 92%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,.06);
        }

        .header h1 {
            margin: 0 0 8px;
        }

        .header p {
            margin: 0;
            color: #6b7280;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,.06);
        }

        .stat-title {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
        }

        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,.06);
            overflow-x: auto;
        }

        .table-card h2 {
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #f9fafb;
            font-size: 14px;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .menunggu {
            background: #fef3c7;
            color: #92400e;
        }

        .diverifikasi {
            background: #dcfce7;
            color: #166534;
        }

        .ditolak {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            background: #2563eb;
            color: white;
        }

        .btn:hover {
            opacity: .85;
        }

        .logout {
            float: right;
            background: #dc2626;
        }

        @media (max-width: 900px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .container {
                width: 95%;
                margin: 20px auto;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .logout {
                float: none;
                display: inline-block;
                margin-top: 15px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <!-- HEADER -->

    <div class="header">

        <a
            href="../auth/logout.php"
            class="btn logout"
        >
            Logout
        </a>

        <h1>
            Dashboard Admin STC 2026
        </h1>

        <p>
            Selamat datang,
            <strong>
                <?= htmlspecialchars($admin["nama_lengkap"]) ?>
            </strong>
        </p>

    </div>


    <!-- STATISTIK -->

    <div class="stats">

        <div class="card">

            <div class="stat-title">
                Total Pendaftaran
            </div>

            <div class="stat-number">
                <?= $total ?>
            </div>

        </div>


        <div class="card">

            <div class="stat-title">
                Menunggu Verifikasi
            </div>

            <div class="stat-number">
                <?= $menunggu ?>
            </div>

        </div>


        <div class="card">

            <div class="stat-title">
                Diverifikasi
            </div>

            <div class="stat-number">
                <?= $diverifikasi ?>
            </div>

        </div>


        <div class="card">

            <div class="stat-title">
                Ditolak
            </div>

            <div class="stat-number">
                <?= $ditolak ?>
            </div>

        </div>

    </div>


    <!-- DAFTAR PENDAFTARAN -->

    <div class="table-card">

        <h2>
            Daftar Pendaftaran
        </h2>

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Kode Pendaftaran</th>

                    <th>Peserta</th>

                    <th>Lomba</th>

                    <th>Tipe</th>

                    <th>Status</th>

                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <?php

                    $statusClass = "menunggu";

                    if ($row["status"] === "DIVERIFIKASI") {
                        $statusClass = "diverifikasi";
                    }

                    if ($row["status"] === "DITOLAK") {
                        $statusClass = "ditolak";
                    }

                    ?>

                    <tr>

                        <td>
                            <?= $row["id"] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row["kode_pendaftaran"]
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row["tim_nama"]
                                ?: (
                                    $row["peserta_nama"]
                                    ?: $row["akun_nama"]
                                )
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row["nama_lomba"]
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row["tipe_pendaftaran"]
                            ) ?>
                        </td>

                        <td>

                            <span class="status <?= $statusClass ?>">

                                <?= htmlspecialchars(
                                    $row["status"]
                                ) ?>

                            </span>

                        </td>

                        <td>

                            <a
                                href="detail.php?id=<?= $row["id"] ?>"
                                class="btn"
                            >
                                Detail
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="7">
                        Belum ada pendaftaran.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>