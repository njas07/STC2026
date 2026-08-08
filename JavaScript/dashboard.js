/* =====================================================
   STC 2026 - Dashboard Peserta
   Mengambil data dari GET /api/registrations/list.php?user_id=
   ===================================================== */

const API_BASE = 'http://localhost/stc2026/api';

document.addEventListener('DOMContentLoaded', function () {
    const user = getStoredUser();
    if (!user) {
        window.location.href = 'multi_page/Login.html';
        return;
    }

    // Tampilkan info user
    document.getElementById('welcomeName').textContent = 'Selamat datang, ' + user.name + '!';
    document.getElementById('greet').textContent = 'Dashboard';
    document.getElementById('subGreet').textContent = user.role.toUpperCase();
    document.getElementById('sideName').textContent = user.name;
    document.getElementById('sideEmail').textContent = user.email;
    document.getElementById('avatarIni').textContent = (user.name.charAt(0) || 'P').toUpperCase();
    document.getElementById('iName').textContent = user.name;
    document.getElementById('iEmail').textContent = user.email;

    loadRegistrations(user.id, user.name);
});

function getStoredUser() {
    try {
        const raw = localStorage.getItem('stc_user');
        return raw ? JSON.parse(raw) : null;
    } catch (e) {
        return null;
    }
}

function logout() {
    localStorage.removeItem('stc_user');
    window.location.href = 'multi_page/Login.html';
}

async function loadRegistrations(userId, userName) {
    try {
        const res = await fetch(API_BASE + '/registrations/list.php?user_id=' + userId);
        const json = await res.json();

        if (!json.success) {
            document.getElementById('emptyState').style.display = 'block';
            return;
        }

        const rows = json.data || [];
        if (rows.length === 0) {
            document.getElementById('emptyState').style.display = 'block';
            return;
        }

        // Ambil pendaftaran terbaru
        const r = rows[0];
        document.getElementById('iSchool').textContent = r.school_name || '-';
        document.getElementById('iCompetition').textContent = formatCompetition(r.competition);
        document.getElementById('iCode').textContent = r.code;

        window._currentReg = r;

        // Status
        setStatus('sReg', r.status);
        setPayStatus('sPay', r.status);
        setStatus('sVer', r.status);

        if (r.status === 'REJECTED' && r.reject_note) {
            const note = document.getElementById('rejectNote');
            note.textContent = 'Catatan Penolakan: ' + r.reject_note;
            note.classList.add('show');
        }
    } catch (e) {
        document.getElementById('emptyState').textContent = 'Gagal memuat data. Pastikan backend berjalan.';
        document.getElementById('emptyState').style.display = 'block';
    }
}

function setStatus(id, status) {
    const el = document.getElementById(id);
    el.className = 'badge ' + status.toLowerCase();
    el.textContent = status;
}

function setPayStatus(id, status) {
    const el = document.getElementById(id);
    if (status === 'PAID' || status === 'VERIFIED') {
        el.className = 'badge paid';
        el.textContent = 'MENUNGGU VERIFIKASI';
    } else {
        el.className = 'badge ' + status.toLowerCase();
        el.textContent = status;
    }
}

function formatCompetition(c) {
    const map = {
        web_design: 'Web Design',
        infografis: 'Infografis',
        excel: 'Microsoft Excel',
        speed_typing: 'Speed Typing',
        mobile_legends: 'Mobile Legends',
        free_fire: 'Free Fire',
        cerdas_cermat: 'Cerdas Cermat'
    };
    return map[c] || c;
}

function showDetail() {
    const r = window._currentReg;
    if (!r) return;

    let html = '<div class="detail-row"><span class="k">Kode</span><span class="v">' + r.code + '</span></div>';
    html += '<div class="detail-row"><span class="k">Nama</span><span class="v">' + (r.name || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">Student ID</span><span class="v">' + (r.student_id || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">Sekolah</span><span class="v">' + (r.school_name || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">Kelas</span><span class="v">' + (r.school_class || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">Email</span><span class="v">' + (r.email || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">WhatsApp</span><span class="v">' + (r.phone || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">Lomba</span><span class="v">' + formatCompetition(r.competition) + '</span></div>';
    html += '<div class="detail-row"><span class="k">Status</span><span class="v">' + r.status + '</span></div>';
    html += '<div class="detail-row"><span class="k">Tanggal Daftar</span><span class="v">' + (r.created_at || '-') + '</span></div>';

    if (r.student_card) {
        html += '<div class="detail-row"><span class="k">Kartu Pelajar</span><span class="v"><a class="file-link" href="' + API_BASE.replace('/api', '') + '/' + r.student_card.replace(/^\//, '') + '" target="_blank">Lihat</a></span></div>';
    }
    if (r.payment_proof) {
        html += '<div class="detail-row"><span class="k">Bukti Bayar</span><span class="v"><a class="file-link" href="' + API_BASE.replace('/api', '') + '/' + r.payment_proof.replace(/^\//, '') + '" target="_blank">Lihat</a></span></div>';
    }

    document.getElementById('detailBody').innerHTML = html;
    document.getElementById('detailModal').classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}
