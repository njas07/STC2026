/* =====================================================
   STC 2026 - Announcement (Public)
   JavaScript mengambil dari: GET /api/announcements
   ===================================================== */

const API_BASE = 'http://localhost/stc2026/api';
const SITE = API_BASE.replace('/api', '');

document.addEventListener('DOMContentLoaded', function () {
    loadAnnouncements();
});

async function loadAnnouncements() {
    const list = document.getElementById('annList');
    try {
        const res = await fetch(API_BASE + '/announcements/list.php');
        const json = await res.json();
        const items = json.success ? json.data : [];

        if (items.length === 0) {
            list.innerHTML = '<div class="empty"><span class="big">☁️</span>Belum ada pengumuman.</div>';
            return;
        }

        list.innerHTML = items.map(function (a) {
            const img = a.image
                ? '<img src="' + SITE + '/' + a.image.replace(/^\//, '') + '" alt="' + escapeHtml(a.title || '') + '">'
                : '';
            const preview = (a.content || '').substring(0, 120) + ((a.content || '').length > 120 ? '...' : '');
            return '<div class="ann-card" onclick="openDetail(' + a.id + ')">' +
                img +
                '<div class="ann-body">' +
                '<h2>' + escapeHtml(a.title || '') + '</h2>' +
                '<div class="ann-meta"><span>📅 ' + (a.created_at || '') + '</span><span class="status">' + a.status + '</span></div>' +
                '<p class="ann-preview">' + escapeHtml(preview) + '</p>' +
                '<span class="read-more">Baca selengkapnya →</span>' +
                '</div></div>';
        }).join('');

        window._anns = items;
    } catch (e) {
        list.innerHTML = '<div class="empty">Gagal memuat pengumuman. Pastikan backend berjalan.</div>';
    }
}

function openDetail(id) {
    const a = window._anns.find(function (x) { return x.id === id; });
    if (!a) return;
    const img = a.image ? '<img src="' + SITE + '/' + a.image.replace(/^\//, '') + '">' : '';
    document.getElementById('modalBody').innerHTML =
        img +
        '<h2>' + escapeHtml(a.title || '') + '</h2>' +
        '<div class="meta">📅 ' + (a.created_at || '') + ' · Status: ' + a.status + '</div>' +
        '<div class="content">' + escapeHtml(a.content || '') + '</div>';
    document.getElementById('detailModal').classList.add('show');
}

function closeModal() {
    document.getElementById('detailModal').classList.remove('show');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
