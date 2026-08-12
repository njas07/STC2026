<?php

/* Halaman ini menampilkan seluruh data pendaftar, jadi wajib admin.
   Sebelumnya hanya mengecek "sudah login", sehingga akun peserta
   biasa pun bisa membuka data seluruh pendaftar. */

require_once "../config/guard.php";

$admin = require_admin(false);

session_write_close();

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Registrasi STC 2026</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f5f5f5;
        }

        h1 {
            margin-bottom: 20px;
        }

        .loading {
            padding: 20px;
            background: white;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #222;
            color: white;
        }

        .status {
            font-weight: bold;
        }

        .error {
            padding: 20px;
            background: #ffdede;
            color: #900;
        }
    </style>

</head>

<body>

<h1>Daftar Pendaftaran STC 2026</h1>

<div id="hasil" class="loading">
    Memuat data...
</div>

<script>

fetch("get_registrations.php", {
    method: "GET",
    credentials: "same-origin",
    cache: "no-store"
})

.then(response => {

    if (!response.ok) {
        throw new Error("HTTP Error: " + response.status);
    }

    return response.json();

})

.then(data => {

    console.log(data);

    if (!data.success) {
        throw new Error(data.message);
    }

    if (data.total === 0) {

        document.getElementById("hasil").innerHTML =
            "<p>Belum ada pendaftaran.</p>";

        return;
    }

    let html = `
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
    `;

    data.data.forEach(item => {

        const registration = item.registration;
        const peserta = item.peserta;
        const tim = item.tim;
        const lomba = item.lomba;

        const namaTampilan =
            tim?.nama_tim ||
            peserta?.nama_lengkap ||
            item.akun?.nama_lengkap ||
            "-";

        html += `
            <tr>

                <td>
                    ${registration.id}
                </td>

                <td>
                    ${registration.kode_pendaftaran}
                </td>

                <td>
                    ${namaTampilan}
                </td>

                <td>
                    ${lomba.nama_lomba}
                </td>

                <td>
                    ${registration.tipe_pendaftaran}
                </td>

                <td class="status">
                    ${registration.status}
                </td>

                <td>
                    <a href="detail.php?id=${registration.id}">
                        Detail
                    </a>
                </td>

            </tr>
        `;

    });

    html += `
            </tbody>

        </table>
    `;

    document.getElementById("hasil").innerHTML = html;

})

.catch(error => {

    console.error(error);

    document.getElementById("hasil").innerHTML = `
        <div class="error">
            <strong>Gagal mengambil data.</strong>
            <br><br>
            ${error.message}
        </div>
    `;

});

</script>

</body>

</html>