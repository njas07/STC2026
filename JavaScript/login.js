/* =====================================================
   STC 2026 - Login
   Frontend → Backend PHP
===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('loginForm');
    const alertBox = document.getElementById('alertBox');

    if (!form) {
        console.error('Form login tidak ditemukan.');
        return;
    }

    form.addEventListener('submit', async function (e) {

        e.preventDefault();

        const email = form.email.value.trim();
        const password = form.password.value;

        /* ==============================
           VALIDASI
        ============================== */

        if (!email || !password) {
            showAlert(
                'Email dan password wajib diisi.',
                'error'
            );
            return;
        }

        /* ==============================
           TOMBOL LOGIN
        ============================== */

        const button = form.querySelector(
            'button[type="submit"]'
        );

        button.disabled = true;
        button.textContent = 'Login...';

        /* ==============================
           KIRIM DATA KE BACKEND
        ============================== */

        try {

            const formData = new FormData();

            formData.append('email', email);
            formData.append('password', password);

            /* ==============================
               API REQUEST
            ============================== */

            const response = await fetch(
                'http://127.0.0.1/stc2026-backend/auth/login.php',
                {
                    method: 'POST',
                    body: formData,
                    credentials: 'include'
                }
            );

            const json = await response.json();

            console.log('Response login:', json);

            /* ==============================
               HASIL LOGIN
            ============================== */

            if (json.success) {

                /* ==============================
                   SIMPAN DATA USER
                ============================== */

                localStorage.setItem(
                    'stc_user',
                    JSON.stringify(json.user)
                );

                showAlert(
                    json.message || 'Login berhasil.',
                    'success'
                );

                /* ==============================
                   REDIRECT BERDASARKAN ROLE
                ============================== */

                setTimeout(function () {

if (json.user.role === 'admin') {

    window.location.href =
        '../admin-dashboard.html';

} else {

    window.location.href =
        '../dashboard.html';

}

                }, 800);

            } else {

                showAlert(
                    json.message || 'Login gagal.',
                    'error'
                );

                button.disabled = false;
                button.textContent = 'Login ☁';
            }

        } catch (error) {

            console.error(
                'Login Error:',
                error
            );

            showAlert(
                'Gagal terhubung ke server. ' +
                'Pastikan backend berjalan.',
                'error'
            );

            button.disabled = false;
            button.textContent = 'Login ☁';
        }

    });


    /* ==============================
       ALERT
    ============================== */

    function showAlert(message, type) {

        if (!alertBox) {
            alert(message);
            return;
        }

        alertBox.textContent = message;

        alertBox.className =
            'alert show ' + type;
    }

});