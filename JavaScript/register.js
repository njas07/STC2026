/* =====================================================
   STC 2026 - Register (Daftar Akun)

   HTML Form
       ↓
   JavaScript
       ↓
   FormData
       ↓
   fetch()
       ↓
   API
       ↓
   MySQL
===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* ==============================
       ELEMENT
    ============================== */

    const form = document.getElementById('registerForm');
    const alertBox = document.getElementById('alertBox');


    /* ==============================
       REGISTER FORM
    ============================== */

    form.addEventListener('submit', async function (e) {

        e.preventDefault();


        /* ==============================
           AMBIL DATA FORM
        ============================== */

        const name = form.name.value.trim();
        const email = form.email.value.trim();
        const phone = form.phone.value.trim();
        const password = form.password.value;
        const confirmPassword = form.confirm_password.value;


        /* ==============================
           VALIDASI EMAIL
        ============================== */

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {

            showAlert(
                'Format email tidak valid.',
                'error'
            );

            return;
        }


        /* ==============================
           VALIDASI PASSWORD
        ============================== */

        if (password.length < 6) {

            showAlert(
                'Password minimal 6 karakter.',
                'error'
            );

            return;
        }


        /* ==============================
           VALIDASI KONFIRMASI PASSWORD
        ============================== */

        if (password !== confirmPassword) {

            showAlert(
                'Konfirmasi password tidak sama.',
                'error'
            );

            return;
        }


        /* ==============================
           TOMBOL SUBMIT
        ============================== */

        const button =
            form.querySelector(
                'button[type="submit"]'
            );

        button.disabled = true;
        button.textContent = 'Mendaftar...';


        /* ==============================
           KIRIM DATA KE BACKEND
        ============================== */

        try {

            const formData = new FormData();

            formData.append('name', name);
            formData.append('email', email);
            formData.append('phone', phone);
            formData.append('password', password);
            formData.append(
                'confirm_password',
                confirmPassword
            );


            /* ==============================
               API REQUEST
            ============================== */

            const response = await fetch(
                'http://localhost/stc2026/api/users/register.php',
                {
                    method: 'POST',
                    body: formData
                }
            );


            /* ==============================
               RESPONSE JSON
            ============================== */

            const json = await response.json();


            /* ==============================
               HASIL REGISTER
            ============================== */

            if (json.success) {

                showAlert(
                    json.message,
                    'success'
                );


                // Pindah ke Login setelah 1,2 detik
                setTimeout(function () {

                    window.location.href =
                        'Login.html';

                }, 1200);

            } else {

                showAlert(
                    json.message,
                    'error'
                );

            }

        } catch (error) {

            console.error(
                'Register Error:',
                error
            );

            showAlert(
                'Gagal terhubung ke server. ' +
                'Pastikan backend berjalan.',
                'error'
            );

        } finally {

            /* ==============================
               AKTIFKAN KEMBALI BUTTON
            ============================== */

            button.disabled = false;
            button.textContent = 'Daftar ☁';

        }

    });


    /* ==============================
       ALERT
    ============================== */

    function showAlert(message, type) {

        alertBox.textContent = message;

        alertBox.className =
            'alert show ' + type;

    }

});