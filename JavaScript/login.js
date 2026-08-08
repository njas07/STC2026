/* =====================================================
   STC 2026 - Login
   HTML Form -> JS -> fetch() -> API -> Redirect by role
   ===================================================== */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    const alertBox = document.getElementById('alertBox');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const email = form.email.value.trim();
        const password = form.password.value;

        if (!email || !password) {
            showAlert('Email dan password wajib diisi.', 'error');
            return;
        }

        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Login...';

        try {
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);

            const res = await fetch('http://localhost/stc2026/api/users/login.php', {
                method: 'POST',
                body: formData
            });
            const json = await res.json();

            if (json.success) {
                // Simpan data user di localStorage
                localStorage.setItem('stc_user', JSON.stringify(json.data));
                showAlert(json.message, 'success');
                setTimeout(function () {
                    window.location.href = json.data.redirect;
                }, 1200);
            } else {
                showAlert(json.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Login';
            }
        } catch (err) {
            showAlert('Gagal terhubung ke server. Pastikan backend berjalan.', 'error');
            btn.disabled = false;
            btn.textContent = 'Login';
        }
    });

    function showAlert(msg, type) {
        alertBox.textContent = msg;
        alertBox.className = 'alert show ' + type;
    }
});
