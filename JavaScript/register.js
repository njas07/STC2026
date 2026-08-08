/* =====================================================
   STC 2026 - Register (Daftar Akun)
   HTML Form -> JS -> FormData -> fetch() -> API -> MySQL
   ===================================================== */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    const alertBox = document.getElementById('alertBox');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const name = form.name.value.trim();
        const email = form.email.value.trim();
        const phone = form.phone.value.trim();
        const password = form.password.value;
        const confirm = form.confirm_password.value;

        // Validasi email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showAlert('Format email tidak valid.', 'error');
            return;
        }

        // Validasi password
        if (password.length < 6) {
            showAlert('Password minimal 6 karakter.', 'error');
            return;
        }

        if (password !== confirm) {
            showAlert('Konfirmasi password tidak sama.', 'error');
            return;
        }

        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Mendaftar...';

        try {
            const formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            formData.append('phone', phone);
            formData.append('password', password);
            formData.append('confirm_password', confirm);

            const res = await fetch('http://localhost/stc2026/api/users/register.php', {
                method: 'POST',
                body: formData
            });
            const json = await res.json();

            if (json.success) {
                showAlert(json.message, 'success');
                setTimeout(function () {
                    window.location.href = 'Login.html';
                }, 1200);
            } else {
                showAlert(json.message, 'error');
            }
        } catch (err) {
            showAlert('Gagal terhubung ke server. Pastikan backend berjalan.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Daftar ☁';
        }
    });

    function showAlert(msg, type) {
        alertBox.textContent = msg;
        alertBox.className = 'alert show ' + type;
    }
});
