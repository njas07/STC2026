/* =====================================================
   STC 2026 - Submission Utility (dipakai semua form lomba)
   - Preview gambar kartu pelajar / bukti
   - Submit via fetch() + FormData ke API
   ===================================================== */

const API_BASE = 'http://localhost/stc2026/api';
const SITE = API_BASE.replace('/api', '');

document.addEventListener('DOMContentLoaded', function () {
    // Preview gambar untuk semua input file bertipe image
    document.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            previewFile(this);
        });
    });

    // Submit handler untuk form dengan data-submit
    document.querySelectorAll('form[data-submit]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            handleSubmit(this);
        });
    });
});

function previewFile(input) {
    const previewId = input.getAttribute('data-preview');
    if (!previewId) return;
    const preview = document.getElementById(previewId);
    if (!preview || !input.files || !input.files[0]) return;

    const file = input.files[0];

    // Validasi ukuran (2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file melebihi 2MB. Silakan pilih file yang lebih kecil.');
        input.value = '';
        preview.classList.remove('show');
        return;
    }

    // Validasi tipe
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (validTypes.indexOf(file.type) === -1) {
        alert('Format tidak diizinkan. Gunakan JPG/PNG/WEBP.');
        input.value = '';
        preview.classList.remove('show');
        return;
    }

    const reader = new FileReader();
    reader.onload = function (ev) {
        preview.src = ev.target.result;
        preview.classList.add('show');
    };
    reader.readAsDataURL(file);
}

async function handleSubmit(form) {
    // Cek checkbox persetujuan
    const checks = form.querySelectorAll('input[type="checkbox"][required]');
    for (let i = 0; i < checks.length; i++) {
        if (!checks[i].checked) {
            alert('Centang semua persetujuan sebelum mengirim.');
            return;
        }
    }

    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    const btnText = btn.textContent;
    btn.textContent = 'Mengirim...';

    const formData = new FormData(form);

    // Ambil user id dari localStorage
    try {
        const user = JSON.parse(localStorage.getItem('stc_user') || 'null');
        formData.append('user_id', user ? user.id : 0);
    } catch (e) {
        formData.append('user_id', 0);
    }

    const competition = form.getAttribute('data-competition');

    // Kumpulkan data khusus lomba menjadi JSON
    const extra = collectExtra(form);
    formData.append('data', JSON.stringify(extra));
    formData.append('competition', competition || 'umum');

    try {
        const res = await fetch(API_BASE + '/registrations/create.php', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();

        if (json.success) {
            form.style.display = 'none';
            showResult(json.data);
        } else {
            alert(json.message || 'Pendaftaran gagal.');
            btn.disabled = false;
            btn.textContent = btnText;
        }
    } catch (err) {
        alert('Gagal terhubung ke server. Pastikan backend berjalan.');
        btn.disabled = false;
        btn.textContent = btnText;
    }
}

// Kumpulkan input bertanda data-field dan kolom umum menjadi objek JSON
function collectExtra(form) {
    const obj = {};
    form.querySelectorAll('[name]').forEach(function (el) {
        if (el.type === 'file' || el.type === 'checkbox') return;
        if (!el.name) return;
        if (['user_id', 'competition', 'name', 'student_id', 'school_name',
             'school_class', 'email', 'phone', 'student_card',
             'instagram_school', 'instagram_school_proof', 'instagram_stc',
             'instagram_stc_proof', 'payment_method', 'amount',
             'transaction_date', 'payment_proof'].indexOf(el.name) !== -1) {
            return; // sudah disimpan di kolom utama
        }
        obj[el.name] = el.value;
    });
    return obj;
}

function showResult(data) {
    let result = document.getElementById('submitResult');
    if (result) {
        document.getElementById('resultCode').textContent = data.code || '-';
        result.classList.add('show');
    }
    // auto scroll ke hasil
    if (result) result.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
