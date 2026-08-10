/* =====================================================
   STC 2026 - Login

   HTML Form
       ↓
   JavaScript
       ↓
   fetch()
       ↓
   API
       ↓
   Login berhasil
       ↓
   Simpan data user
       ↓
   Redirect berdasarkan role
===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* ==============================
       ELEMENT
    ============================== */

    const form = document.getElementById('loginForm');
    const alertBox = document.getElementById('alertBox');


    /* ==============================
       LOGIN FORM
    ============================== */

    form.addEventListener('submit', async function (e) {

        e.preventDefault();


        /* ==============================
           AMBIL DATA FORM
        ============================== */

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

        const button =
            form.querySelector(
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
                'http://localhost/stc2026/api/users/login.php',
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
               HASIL LOGIN
            ============================== */

            if (json.success) {

                /* ==============================
                   SIMPAN DATA USER
                ============================== */

                localStorage.setItem(
                    'stc_user',
                    JSON.stringify(json.data)
                );


                /* ==============================
                   LOGIN BERHASIL
                ============================== */

                showAlert(
                    json.message,
                    'success'
                );


                /* ==============================
                   REDIRECT BERDASARKAN ROLE
                ============================== */

                setTimeout(function () {

                    window.location.href =
                        json.data.redirect;

                }, 1200);

            } else {

                /* ==============================
                   LOGIN GAGAL
                ============================== */

                showAlert(
                    json.message,
                    'error'
                );

                button.disabled = false;
                button.textContent = 'Login ☁';

            }

        } catch (error) {

            /* ==============================
               CONNECTION ERROR
            ============================== */

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

        alertBox.textContent = message;

        alertBox.className =
            'alert show ' + type;

    }

});