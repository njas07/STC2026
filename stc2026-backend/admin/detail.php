<?php

session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "secure" => false,
    "httponly" => true,
    "samesite" => "Lax"
]);

session_start();

if (!isset($_SESSION["user_id"])) {
    die("Silakan login sebagai admin.");
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    die("Akses hanya untuk admin.");
}

session_write_close();

$registration_id = $_GET["id"] ?? null;

if (!$registration_id || !ctype_digit((string)$registration_id)) {
    die("Registration ID tidak ditemukan.");
}

session_write_close();

$registration_id = $_GET["id"] ?? null;

if (!$registration_id || !ctype_digit((string)$registration_id)) {
    die("Registration ID tidak ditemukan.");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftaran STC 2026</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            margin-bottom: 25px;
        }

        h2 {
            margin-top: 0;
        }

        .card {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .label {
            font-size: 13px;
            color: #666;
        }

        .value {
            font-weight: bold;
            margin-top: 4px;
            word-break: break-word;
        }

        .status {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 6px;
            font-weight: bold;
        }

        .waiting {
            background: #fff3cd;
            color: #856404;
        }

        .valid {
            background: #d1e7dd;
            color: #0f5132;
        }

        .invalid {
            background: #f8d7da;
            color: #842029;
        }

        .document {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .document a {
            display: inline-block;
            margin-top: 8px;
        }

        .member {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 12px;
            background: #fff;
        }

        .member-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }


        .empty {
            color: #666;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        button {
            border: none;
            padding: 12px 18px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
        }

        .verify {
            background: #198754;
            color: white;
        }

        .reject {
            background: #dc3545;
            color: white;
        }

        .back {
            background: #6c757d;
            color: white;
        }

        button:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        #loading {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 15px;
            border-radius: 8px;
        }

        @media (max-width: 700px) {
            body {
                padding: 15px;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Detail Pendaftaran STC 2026</h1>

    <div id="loading">
        Memuat data...
    </div>

    <div id="content" style="display:none;">

        <!-- PENDAFTARAN -->
        <div class="card">
            <h2>📋 Pendaftaran</h2>

            <div class="grid">
                <div class="item">
                    <div class="label">Kode Pendaftaran</div>
                    <div class="value" id="kode"></div>
                </div>

                <div class="item">
                    <div class="label">Lomba</div>
                    <div class="value" id="lomba"></div>
                </div>

                <div class="item">
                    <div class="label">Tipe Pendaftaran</div>
                    <div class="value" id="tipe"></div>
                </div>

                <div class="item">
                    <div class="label">Status</div>
                    <div id="status"></div>
                </div>
            </div>
        </div>

        <!-- AKUN -->
        <div class="card">
            <h2>👤 Akun</h2>

            <div class="grid">
                <div class="item">
                    <div class="label">Nama</div>
                    <div class="value" id="nama-akun"></div>
                </div>

                <div class="item">
                    <div class="label">Email</div>
                    <div class="value" id="email-akun"></div>
                </div>

                <div class="item">
                    <div class="label">WhatsApp</div>
                    <div class="value" id="whatsapp-akun"></div>
                </div>
            </div>
        </div>

        <!-- PESERTA INDIVIDU -->
        <div class="card" id="individual-card" style="display:none;">
            <h2>🧑 Data Peserta</h2>

            <div class="grid">
                <div class="item">
                    <div class="label">Nama</div>
                    <div class="value" id="nama-peserta"></div>
                </div>

                <div class="item">
                    <div class="label">NIS/NISN</div>
                    <div class="value" id="nis"></div>
                </div>

                <div class="item">
                    <div class="label">Sekolah</div>
                    <div class="value" id="sekolah"></div>
                </div>

                <div class="item">
                    <div class="label">Kelas</div>
                    <div class="value" id="kelas"></div>
                </div>

                <div class="item">
                    <div class="label">Email</div>
                    <div class="value" id="email-peserta"></div>
                </div>

                <div class="item">
                    <div class="label">WhatsApp</div>
                    <div class="value" id="whatsapp-peserta"></div>
                </div>
            </div>
        </div>

        <!-- DATA TIM -->
        <div class="card" id="team-card" style="display:none;">
            <h2>👥 Data Tim</h2>

            <div id="team-info" class="grid"></div>

            <hr style="margin:20px 0; border:0; border-top:1px solid #ddd;">

            <h3>👤 Anggota Tim</h3>
            <div id="team-members"></div>
        </div>

        <!-- DOKUMEN -->
        <div class="card">
            <h2>📄 Dokumen</h2>
            <div id="documents"></div>
        </div>

        <!-- PAYMENT -->
        <div class="card">
            <h2>💰 Pembayaran</h2>
            <div id="payment"></div>
        </div>

        <!-- ACTION -->
        <div class="card">
            <h2>⚙️ Verifikasi</h2>

            <div class="actions">
                <button class="verify" id="btn-verify">
                    Verifikasi Pendaftaran
                </button>

                <button class="reject" id="btn-reject">
                    Tolak Pendaftaran
                </button>

                <button class="back" onclick="history.back()">
                    Kembali
                </button>
            </div>
        </div>

    </div>
</div>

<script>
const registrationId = <?= json_encode($registration_id) ?>;

/* ================================
   HELPER
================================ */

function safe(value) {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    return String(value);
}

function getStatusClass(status) {
    if (status === "VALID" || status === "DIVERIFIKASI") {
        return "valid";
    }

    if (status === "TIDAK_VALID" || status === "DITOLAK") {
        return "invalid";
    }

    return "waiting";
}

/* ================================
   LOAD DETAIL
================================ */

async function loadDetail() {
    try {
        const response = await fetch(
            "detail_api.php?id=" + encodeURIComponent(registrationId),
            {
                credentials: "same-origin",
                cache: "no-store"
            }
        );

        if (!response.ok) {
            throw new Error("HTTP " + response.status);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || "Gagal mengambil data.");
        }

        renderData(data);

        document.getElementById("loading").style.display = "none";
        document.getElementById("content").style.display = "block";

    } catch (error) {
        document.getElementById("loading").innerHTML =
            `<div class="error">
                Gagal memuat data:<br>
                ${safe(error.message)}
            </div>`;
    }
}

/* ================================
   RENDER DATA
================================ */

function renderData(data) {
    const r = data.registration || {};

    document.getElementById("kode").textContent =
        safe(r.kode_pendaftaran);

    document.getElementById("lomba").textContent =
        safe(data.lomba?.nama_lomba);

    document.getElementById("tipe").textContent =
        safe(r.tipe_pendaftaran);

    document.getElementById("status").innerHTML =
        `<span class="status ${getStatusClass(r.status)}">
            ${safe(r.status)}
        </span>`;

    /* AKUN */
    document.getElementById("nama-akun").textContent =
        safe(data.akun?.nama_lengkap);

    document.getElementById("email-akun").textContent =
        safe(data.akun?.email);

    document.getElementById("whatsapp-akun").textContent =
        safe(data.akun?.no_whatsapp);

    /* INDIVIDU */
    renderIndividual(data);

    /* TIM */
    renderTeam(data);

    /* DOCUMENTS */
    renderDocuments(data.documents || []);

    /* PAYMENT */
    renderPayment(data.payment);
}

/* ================================
   INDIVIDU
================================ */

function renderIndividual(data) {
    const card = document.getElementById("individual-card");
    const peserta = data.peserta_individu || data.peserta;

    if (!peserta) {
        card.style.display = "none";
        return;
    }

    card.style.display = "block";

    document.getElementById("nama-peserta").textContent =
        safe(peserta.nama_lengkap);

    document.getElementById("nis").textContent =
        safe(peserta.nis_nisn);

    document.getElementById("sekolah").textContent =
        safe(peserta.asal_instansi_sekolah);

    document.getElementById("kelas").textContent =
        safe(peserta.kelas);

    document.getElementById("email-peserta").textContent =
        safe(peserta.email);

    document.getElementById("whatsapp-peserta").textContent =
        safe(peserta.no_whatsapp);
}

/* ================================
   TIM
================================ */

function renderTeam(data) {
    const card = document.getElementById("team-card");

    /*
     * Mendukung beberapa nama key agar cocok dengan API
     * yang sudah dibuat sebelumnya.
     */
    const team =
        data.team_registration ||
        data.team ||
        data.data_tim ||
        null;

    const members =
        data.team_members ||
        data.members ||
        data.anggota_tim ||
        [];

    if (!team && (!Array.isArray(members) || members.length === 0)) {
        card.style.display = "none";
        return;
    }

    card.style.display = "block";

    const teamInfo = document.getElementById("team-info");

    teamInfo.innerHTML = `
        <div class="item">
            <div class="label">Nama Tim</div>
            <div class="value">${safe(team?.nama_tim)}</div>
        </div>

        <div class="item">
            <div class="label">Nama Sekolah</div>
            <div class="value">${safe(team?.nama_sekolah)}</div>
        </div>

        <div class="item">
            <div class="label">Email</div>
            <div class="value">${safe(team?.email)}</div>
        </div>

        <div class="item">
            <div class="label">WhatsApp</div>
            <div class="value">${safe(team?.no_whatsapp)}</div>
        </div>
    `;

    const membersContainer =
        document.getElementById("team-members");

    if (!Array.isArray(members) || members.length === 0) {
        membersContainer.innerHTML =
            `<p class="empty">Belum ada anggota tim.</p>`;
        return;
    }

    membersContainer.innerHTML = members.map((member, index) => `
        <div class="member">
            <div class="member-title">
                Anggota ${safe(member.nomor_anggota || (index + 1))}
            </div>

            <div class="grid">
                <div class="item">
                    <div class="label">Nama Lengkap</div>
                    <div class="value">
                        ${safe(member.nama_lengkap)}
                    </div>
                </div>

                <div class="item">
                    <div class="label">NIS/NISN</div>
                    <div class="value">
                        ${safe(member.nis_nisn)}
                    </div>
                </div>

                <div class="item">
                    <div class="label">Kelas</div>
                    <div class="value">
                        ${safe(member.kelas)}
                    </div>
                </div>
            </div>
        </div>
    `).join("");
}

/* ================================
   DOCUMENTS
================================ */

function renderDocuments(documents) {
    const container = document.getElementById("documents");

    if (!documents.length) {
        container.innerHTML =
            "<p class='empty'>Belum ada dokumen.</p>";
        return;
    }

    container.innerHTML = documents.map(doc => `
        <div class="document">
            <strong>${safe(doc.jenis_dokumen)}</strong>
            <br>
            ${safe(doc.nama_file_asli)}
            <br>
            <a href="../${encodeURI(doc.file_path || "")}"
               target="_blank">
                Lihat Dokumen
            </a>
        </div>
    `).join("");
}

/* ================================
   PAYMENT
================================ */

function renderPayment(payment) {
    const container = document.getElementById("payment");

    if (!payment) {
        container.innerHTML =
            "<p class='empty'>Belum ada pembayaran.</p>";
        return;
    }

    const jumlah = Number(payment.jumlah_bayar || 0)
        .toLocaleString("id-ID");

    container.innerHTML = `
        <div class="grid">
            <div class="item">
                <div class="label">Metode</div>
                <div class="value">
                    ${safe(payment.metode_pembayaran)}
                </div>
            </div>

            <div class="item">
                <div class="label">Jumlah</div>
                <div class="value">
                    Rp ${jumlah}
                </div>
            </div>

            <div class="item">
                <div class="label">Tanggal Pembayaran</div>
                <div class="value">
                    ${safe(payment.tanggal_pembayaran)}
                </div>
            </div>

            <div class="item">
                <div class="label">Status Pembayaran</div>
                <div>
                    <span class="status ${getStatusClass(payment.status_pembayaran)}">
                        ${safe(payment.status_pembayaran)}
                    </span>
                </div>
            </div>
        </div>

        <br>

        <a href="../${encodeURI(payment.bukti_pembayaran || "")}"
           target="_blank">
            Lihat Bukti Pembayaran
        </a>
    `;
}

/* ================================
   UPDATE STATUS
================================ */

document.getElementById("btn-verify").addEventListener(
    "click",
    () => updateStatus("DIVERIFIKASI")
);

document.getElementById("btn-reject").addEventListener(
    "click",
    () => updateStatus("DITOLAK")
);

async function updateStatus(status) {
    const verifyButton = document.getElementById("btn-verify");
    const rejectButton = document.getElementById("btn-reject");

    const tombol =
        status === "DIVERIFIKASI"
            ? verifyButton
            : rejectButton;

    const teksAwal = tombol.innerText;

    const konfirmasi = confirm(
        status === "DIVERIFIKASI"
            ? "Apakah kamu yakin ingin memverifikasi pendaftaran ini?"
            : "Apakah kamu yakin ingin menolak pendaftaran ini?"
    );

    if (!konfirmasi) {
        return;
    }

    verifyButton.disabled = true;
    rejectButton.disabled = true;
    tombol.innerText = "Memproses...";

    try {
        const formData = new FormData();

        formData.append("registration_id", registrationId);
        formData.append("status", status);

        const response = await fetch(
            "update_status.php",
            {
                method: "POST",
                body: formData,
                credentials: "same-origin",
                cache: "no-store"
            }
        );

        if (!response.ok) {
            throw new Error(
                "Server mengembalikan HTTP " + response.status
            );
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(
                result.message ||
                "Gagal memperbarui status."
            );
        }

        alert(
            "Berhasil!\n\n" +
            "Status pendaftaran sekarang: " +
            (result.status || result.status_pendaftaran || status)
        );

        window.location.reload();

    } catch (error) {
        console.error("Update status error:", error);

        alert(
            "Gagal mengubah status pendaftaran.\n\n" +
            error.message
        );

        verifyButton.disabled = false;
        rejectButton.disabled = false;
        tombol.innerText = teksAwal;
    }
}

loadDetail();
</script>

</body>
</html>