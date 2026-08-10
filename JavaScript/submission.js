/* =====================================================
   STC 2026 - Submission Utility
   Dipakai untuk form pendaftaran lomba
   ===================================================== */

const API_BASE = 'http://localhost/stc2026/api';
const SITE = API_BASE.replace('/api', '');


/* =====================================================
   SAAT HALAMAN SELESAI DIMUAT
   ===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* ==============================
       VALIDASI FILE
    ============================== */

    document.querySelectorAll('input[type="file"]').forEach(function (input) {

        input.addEventListener('change', function () {
            previewFile(this);
        });

    });


    /* ==============================
       SEMBUNYIKAN HASIL SUBMIT
       ============================== */

    document.querySelectorAll('.submission-result').forEach(function (result) {
        result.style.display = 'none';
    });


    /* ==============================
       FORM SUBMIT
       ============================== */

    document.querySelectorAll('form[data-submit]').forEach(function (form) {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            handleSubmit(this);

        });

    });

});


/* =====================================================
   PREVIEW + VALIDASI FILE
   ===================================================== */

function previewFile(input) {

    if (!input.files || !input.files[0]) {
        return true;
    }

    const file = input.files[0];


    /* ==============================
       MAKSIMAL 2 MB
       ============================== */

    if (file.size > 2 * 1024 * 1024) {

        alert(
            'Ukuran file melebihi 2MB. Silakan pilih file yang lebih kecil.'
        );

        input.value = '';

        hidePreview(input);

        return false;
    }


    /* ==============================
       FORMAT FILE
       ============================== */

    const validTypes = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp',
        'application/pdf'
    ];


    if (validTypes.indexOf(file.type) === -1) {

        alert(
            'Format tidak diizinkan. Gunakan JPG, PNG, WEBP, atau PDF.'
        );

        input.value = '';

        hidePreview(input);

        return false;
    }


    /* ==============================
       PREVIEW GAMBAR
       ============================== */

    const previewId =
        input.getAttribute('data-preview');

    const preview =
        previewId
            ? document.getElementById(previewId)
            : null;


    /*
       PDF tidak bisa ditampilkan
       sebagai preview gambar.
    */

    if (
        preview &&
        file.type !== 'application/pdf'
    ) {

        const reader = new FileReader();

        reader.onload = function (event) {

            preview.src = event.target.result;

            preview.classList.add('show');

        };

        reader.readAsDataURL(file);

    } else {

        hidePreview(input);

    }


    return true;
}


/* =====================================================
   HAPUS PREVIEW
   ===================================================== */

function hidePreview(input) {

    const previewId =
        input.getAttribute('data-preview');

    if (!previewId) {
        return;
    }


    const preview =
        document.getElementById(previewId);


    if (preview) {

        preview.src = '';

        preview.classList.remove('show');

    }

}


/* =====================================================
   HANDLE SUBMIT
   ===================================================== */

async function handleSubmit(form) {


    /* ==============================
       CEK CHECKBOX
       ============================== */

    const checks =
        form.querySelectorAll(
            'input[type="checkbox"][required]'
        );


    for (let i = 0; i < checks.length; i++) {

        if (!checks[i].checked) {

            alert(
                'Centang semua persetujuan sebelum mengirim.'
            );

            return;
        }

    }


    /* ==============================
       TOMBOL SUBMIT
       ============================== */

    const btn =
        form.querySelector(
            'button[type="submit"]'
        );


    if (!btn) {
        return;
    }


    btn.disabled = true;


    const btnText =
        btn.textContent;


    btn.textContent =
        'Mengirim...';


    /* ==============================
       FORM DATA
       ============================== */

    const formData =
        new FormData(form);


    /* ==============================
       USER ID
       ============================== */

    try {

        const user =
            JSON.parse(
                localStorage.getItem('stc_user') || 'null'
            );


        formData.append(
            'user_id',
            user ? user.id : 0
        );

    } catch (error) {

        formData.append(
            'user_id',
            0
        );

    }


    /* ==============================
       NAMA LOMBA
       ============================== */

    const competition =
        form.getAttribute(
            'data-competition'
        );


    /* ==============================
       DATA TAMBAHAN
       ============================== */

    const extra =
        collectExtra(form);


    formData.append(
        'data',
        JSON.stringify(extra)
    );


    formData.append(
        'competition',
        competition || 'umum'
    );


    /* =================================================
       KIRIM KE BACKEND
       ================================================= */

    try {

        const res =
            await fetch(
                API_BASE +
                '/registrations/create.php',
                {
                    method: 'POST',
                    body: formData
                }
            );


        const json =
            await res.json();


        /* ==============================
           BERHASIL
           ============================== */

        if (json.success) {


            /*
               Jangan menyembunyikan seluruh form.
               Hanya tombol submit yang disembunyikan.
            */

            form
                .querySelectorAll(
                    '.form-actions'
                )
                .forEach(function (actions) {

                    actions.style.display =
                        'none';

                });


            /*
               Nonaktifkan input
               setelah berhasil.
            */

            form
                .querySelectorAll(
                    'input, select, textarea, button'
                )
                .forEach(function (field) {

                    field.disabled = true;

                });


            /* ==============================
               TAMPILKAN HASIL
               ============================== */

            showResult(
                json.data
            );

        }


        /* ==============================
           GAGAL
           ============================== */

        else {

            alert(
                json.message ||
                'Pendaftaran gagal.'
            );


            btn.disabled =
                false;


            btn.textContent =
                btnText;

        }

    }


    /* ==============================
       ERROR SERVER
       ============================== */

    catch (error) {

        console.error(
            'Submission error:',
            error
        );


        alert(
            'Gagal terhubung ke server. ' +
            'Pastikan backend berjalan.'
        );


        btn.disabled =
            false;


        btn.textContent =
            btnText;

    }

}


/* =====================================================
   KUMPULKAN DATA TAMBAHAN
   ===================================================== */

function collectExtra(form) {

    const obj = {};


    form
        .querySelectorAll('[name]')
        .forEach(function (el) {


            /* ==============================
               FILE TIDAK MASUK JSON
               ============================== */

            if (el.type === 'file') {
                return;
            }


            /* ==============================
               CHECKBOX TIDAK MASUK JSON
               ============================== */

            if (el.type === 'checkbox') {
                return;
            }


            if (!el.name) {
                return;
            }


            /* ==============================
               FIELD UTAMA
               ============================== */

            const mainFields = [

                'user_id',

                'competition',

                'name',

                'student_id',

                'school_name',

                'school_class',

                'email',

                'phone',

                'student_card',

                'instagram_school',

                'instagram_school_proof',

                'instagram_stc',

                'instagram_stc_proof',

                'payment_method',

                'amount',

                'transaction_date',

                'payment_proof'

            ];


            if (
                mainFields.indexOf(
                    el.name
                ) !== -1
            ) {

                return;

            }


            /* ==============================
               SIMPAN DATA LAIN
               ============================== */

            obj[el.name] =
                el.value;

        });


    return obj;

}


/* =====================================================
   HASIL PENDAFTARAN
   ===================================================== */

function showResult(data) {


    const result =
        document.getElementById(
            'submitResult'
        );


    if (!result) {
        return;
    }


    const resultCode =
        document.getElementById(
            'resultCode'
        );


    if (resultCode) {

        resultCode.textContent =
            data.code || '-';

    }


    /* ==============================
       TAMPILKAN HASIL
       ============================== */

    result.style.display =
        'block';


    result.classList.add(
        'show'
    );


    /* ==============================
       SCROLL KE HASIL
       ============================== */

    result.scrollIntoView({

        behavior: 'smooth',

        block: 'center'

    });

}