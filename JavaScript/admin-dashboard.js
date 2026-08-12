/* =====================================================
   STC 2026 - Admin / Panitia Dashboard
   ===================================================== */

const API_BASE = 'http://localhost/stc2026/api';
let _regsCache = [];

document.addEventListener('DOMContentLoaded', function () {
    /* Hak akses ditentukan server (session PHP + tabel users),
       bukan localStorage yang bisa dipalsukan dari console. */
    STCGuard.requireAdmin().then(mulai).catch(function () {
        /* STCGuard sudah mengalihkan ke halaman login. */
    });
});

function mulai(user) {

    document.getElementById('sideName').textContent = user.name;
    document.getElementById('sideEmail').textContent = user.email;
    document.getElementById('avatarIni').textContent = (user.name.charAt(0) || 'A').toUpperCase();

    // Tab switching
    document.querySelectorAll('.side-nav a').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('.side-nav a').forEach(function (x) { x.classList.remove('active'); });
            a.classList.add('active');
            document.querySelectorAll('.tab-panel').forEach(function (p) { p.style.display = 'none'; });
            document.getElementById('tab-' + a.dataset.tab).style.display = 'block';
        });
    });

    loadRegistrations();
    loadAnnouncements();

    // Announcement submit
    document.getElementById('annForm').addEventListener('submit', function (e) {
        e.preventDefault();
        saveAnnouncement();
    });
}

function logout() {
    /* Session di server ikut dihapus, bukan cuma localStorage. */
    STCGuard.logout();
}

/* ================= REGISTRATIONS ================= */
async function loadRegistrations() {
    const search = document.getElementById('searchInput').value;
    const comp = document.getElementById('filterCompetition').value;
    const status = document.getElementById('filterStatus').value;

    let url = API_BASE + '/registrations/list.php?all=1';
    if (comp) url += '&competition=' + encodeURIComponent(comp);
    if (status) url += '&status=' + encodeURIComponent(status);
    if (search) url += '&search=' + encodeURIComponent(search);

    try {
        const res = await fetch(url);
        const json = await res.json();
        _regsCache = json.success ? json.data : [];
        renderTable();
    } catch (e) {
        document.getElementById('regTableBody').innerHTML = '<tr><td colspan="6" class="empty">Gagal memuat data.</td></tr>';
    }
}

function renderTable() {
    const tbody = document.getElementById('regTableBody');
    if (_regsCache.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty">Tidak ada data.</td></tr>';
        return;
    }
    tbody.innerHTML = _regsCache.map(function (r) {
        return '<tr>' +
            '<td>' + r.code + '</td>' +
            '<td>' + (r.name || '-') + '</td>' +
            '<td>' + formatCompetition(r.competition) + '</td>' +
            '<td>' + (r.school_name || '-') + '</td>' +
            '<td><span class="badge ' + (r.status || 'PENDING').toLowerCase() + '">' + r.status + '</span></td>' +
            '<td><button class="btn btn-view" onclick="viewDetail(' + r.id + ')">Lihat</button></td>' +
        '</tr>';
    }).join('');
}

function viewDetail(id) {
    const r = _regsCache.find(function (x) { return x.id === id; });
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
    html += '<div class="detail-row"><span class="k">Metode Bayar</span><span class="v">' + (r.payment_method || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">Jumlah</span><span class="v">' + (r.amount || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">Tgl Bayar</span><span class="v">' + (r.transaction_date || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">IG Sekolah</span><span class="v">' + (r.instagram_school || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="k">IG STC</span><span class="v">' + (r.instagram_stc || '-') + '</span></div>';

    // Files
    if (r.student_card) html += fileRow('Kartu Pelajar', r.student_card);
    if (r.instagram_school_proof) html += fileRow('Bukti IG Sekolah', r.instagram_school_proof);
    if (r.instagram_stc_proof) html += fileRow('Bukti IG STC', r.instagram_stc_proof);
    if (r.payment_proof) html += fileRow('Bukti Pembayaran', r.payment_proof);

    if (r.reject_note) {
        document.getElementById('rejectNote').textContent = 'Catatan: ' + r.reject_note;
        document.getElementById('rejectNote').classList.add('show');
    } else {
        document.getElementById('rejectNote').classList.remove('show');
    }

    window._activeRegId = r.id;
    document.getElementById('detailBody').innerHTML = html;
    document.getElementById('detailModal').classList.add('show');
}

function fileRow(label, path) {
    return '<div class="detail-row"><span class="k">' + label + '</span>' +
        '<span class="v"><a class="file-link" target="_blank" href="' + API_BASE.replace('/api', '') + '/' + path.replace(/^\//, '') + '">Lihat File</a></span></div>';
}

function formatCompetition(c) {
    const map = {
        web_design: 'Web Design', infografis: 'Infografis', excel: 'Microsoft Excel',
        speed_typing: 'Speed Typing', mobile_legends: 'Mobile Legends',
        free_fire: 'Free Fire', cerdas_cermat: 'Cerdas Cermat'
    };
    return map[c] || c;
}

async function verifyReg(action) {
    const id = window._activeRegId;
    if (!id) return;
    try {
        const res = await fetch(API_BASE + '/registrations/verify.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id, action: action })
        });
        const json = await res.json();
        alert(json.message);
        closeModal('detailModal');
        loadRegistrations();
    } catch (e) {
        alert('Gagal. Pastikan backend berjalan.');
    }
}

function promptReject() {
    const id = window._activeRegId;
    const note = prompt('Catatan penolakan:');
    if (note === null) return;
    (async function () {
        try {
            const res = await fetch(API_BASE + '/registrations/verify.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id, action: 'reject', reject_note: note })
            });
            const json = await res.json();
            alert(json.message);
            closeModal('detailModal');
            loadRegistrations();
        } catch (e) {
            alert('Gagal.');
        }
    })();
}

/* ================= ANNOUNCEMENTS ================= */
async function loadAnnouncements() {
    const list = document.getElementById('annList');
    try {
        const res = await fetch(API_BASE + '/announcements/list.php?all=1');
        const json = await res.json();
        const items = json.success ? json.data : [];
        if (items.length === 0) {
            list.innerHTML = '<div class="empty">Belum ada pengumuman.</div>';
            return;
        }
        list.innerHTML = items.map(function (a) {
            return '<div class="ann-item">' +
                '<div><div class="title">' + (a.title || '') + '</div>' +
                '<div class="meta">' + (a.created_at || '') + '</div>' +
                '<span class="status ' + a.status + '">' + a.status + '</span></div>' +
                '<div>' +
                '<button class="btn btn-view" onclick="editAnn(' + a.id + ')">Edit</button> ' +
                '<button class="btn btn-reject" onclick="deleteAnn(' + a.id + ')">Hapus</button>' +
                '</div></div>';
        }).join('');
    } catch (e) {
        list.innerHTML = '<div class="empty">Gagal memuat pengumuman.</div>';
    }
}

function editAnn(id) {
    fetch(API_BASE + '/announcements/list.php?all=1').then(function (res) { return res.json(); })
    .then(function (json) {
        const a = json.data.find(function (x) { return x.id === id; });
        if (a) {
            document.getElementById('annId').value = a.id;
            document.getElementById('annTitle').value = a.title;
            document.getElementById('annContent').value = a.content || '';
            document.getElementById('annStatus').value = a.status;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
}

function deleteAnn(id) {
    if (!confirm('Hapus pengumuman ini?')) return;
    (async function () {
        try {
            const res = await fetch(API_BASE + '/announcements/delete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id })
            });
            const json = await res.json();
            alert(json.message);
            loadAnnouncements();
        } catch (e) { alert('Gagal.'); }
    })();
}

async function saveAnnouncement() {
    const id = document.getElementById('annId').value;
    const url = id ? API_BASE + '/announcements/update.php' : API_BASE + '/announcements/create.php';

    if (id) {
        // update via json
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    id: id,
                    title: document.getElementById('annTitle').value,
                    content: document.getElementById('annContent').value,
                    status: document.getElementById('annStatus').value
                })
            });
            const json = await res.json();
            alert(json.message);
            resetAnnForm();
            loadAnnouncements();
        } catch (e) { alert('Gagal.'); }
    } else {
        // create via formdata (dengan file)
        const fd = new FormData();
        fd.append('title', document.getElementById('annTitle').value);
        fd.append('content', document.getElementById('annContent').value);
        fd.append('status', document.getElementById('annStatus').value);
        const img = document.getElementById('annImage').files[0];
        if (img) fd.append('image', img);

        try {
            const res = await fetch(url, { method: 'POST', body: fd });
            const json = await res.json();
            alert(json.message);
            resetAnnForm();
            loadAnnouncements();
        } catch (e) { alert('Gagal.'); }
    }
}

function resetAnnForm() {
    document.getElementById('annId').value = '';
    document.getElementById('annTitle').value = '';
    document.getElementById('annContent').value = '';
    document.getElementById('annStatus').value = 'draft';
    document.getElementById('annImage').value = '';
}

/* ============ MODAL HELPERS ============ */
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}
