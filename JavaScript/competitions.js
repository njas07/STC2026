/* =========================================================
   STC 2026
   CERDAS CERMAT - COMPETITION JAVASCRIPT
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       FORM
    ===================================================== */

    const form = document.getElementById("cerdasCermatForm");

    // Jika form tidak ditemukan, hentikan JS
    if (!form) {
        return;
    }


    /* =====================================================
       SUBMIT FORM
    ===================================================== */

    form.addEventListener("submit", async function (e) {

        e.preventDefault();


        /* =================================================
           AMBIL DATA ANGGOTA
        ================================================= */

        const member1Name =
            form.member1_name.value.trim();

        const member1Class =
            form.member1_class.value.trim();

        const member1StudentId =
            form.member1_student_id.value.trim();


        const member2Name =
            form.member2_name.value.trim();

        const member2Class =
            form.member2_class.value.trim();

        const member2StudentId =
            form.member2_student_id.value.trim();


        const member3Name =
            form.member3_name.value.trim();

        const member3Class =
            form.member3_class.value.trim();

        const member3StudentId =
            form.member3_student_id.value.trim();


        /* =================================================
           VALIDASI
        ================================================= */

        if (
            !member1Name ||
            !member1Class ||
            !member1StudentId ||
            !member2Name ||
            !member2Class ||
            !member2StudentId ||
            !member3Name ||
            !member3Class ||
            !member3StudentId
        ) {

            showAlert(
                "Semua data anggota tim wajib diisi.",
                "error"
            );

            return;
        }


        /* =================================================
           TOMBOL SUBMIT
        ================================================= */

        const submitButton =
            form.querySelector(
                'button[type="submit"]'
            );

        if (submitButton) {

            submitButton.disabled = true;

            submitButton.textContent =
                "Mengirim...";
        }


        /* =================================================
           FORM DATA
        ================================================= */

        const formData = new FormData(form);


        /* =================================================
           KIRIM KE BACKEND
        ================================================= */

        try {

            const response = await fetch(
                "http://localhost/stc2026/api/competitions/register.php",
                {
                    method: "POST",
                    body: formData
                }
            );


            /* =============================================
               CEK RESPONSE
            ============================================= */

            if (!response.ok) {

                throw new Error(
                    "Server mengembalikan error."
                );
            }


            const json =
                await response.json();


            /* =============================================
               RESPONSE BERHASIL
            ============================================= */

            if (json.success) {

                showAlert(
                    json.message ||
                    "Pendaftaran berhasil.",
                    "success"
                );


                /* =========================================
                   RESET FORM
                ========================================= */

                form.reset();


                /* =========================================
                   REDIRECT
                ========================================= */

                if (json.data && json.data.redirect) {

                    setTimeout(function () {

                        window.location.href =
                            json.data.redirect;

                    }, 1200);

                }

            } else {

                showAlert(
                    json.message ||
                    "Pendaftaran gagal.",
                    "error"
                );

            }


        } catch (error) {

            console.error(
                "Competition Error:",
                error
            );


            showAlert(
                "Gagal terhubung ke server. Pastikan backend berjalan.",
                "error"
            );


        } finally {

            /* =============================================
               AKTIFKAN KEMBALI TOMBOL
            ============================================= */

            if (submitButton) {

                submitButton.disabled = false;

                submitButton.textContent =
                    "Daftar Tim ☁";
            }

        }

    });


    /* =====================================================
       ALERT
    ===================================================== */

    function showAlert(message, type) {

        let alertBox =
            document.getElementById("alertBox");


        /* ================================================
           JIKA ALERT BELUM ADA
        ================================================ */

        if (!alertBox) {

            alertBox =
                document.createElement("div");

            alertBox.id = "alertBox";

            alertBox.className = "alert";


            form.parentNode.insertBefore(
                alertBox,
                form
            );
        }


        /* ================================================
           ISI ALERT
        ================================================ */

        alertBox.textContent = message;

        alertBox.className =
            "alert show " + type;


        /* ================================================
           HILANGKAN ALERT SETELAH BEBERAPA DETIK
        ================================================ */

        setTimeout(function () {

            alertBox.classList.remove("show");

        }, 4000);

    }

});