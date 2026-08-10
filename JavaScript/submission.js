/* =====================================================
   STC 2026 - SUBMISSION + TEAM MEMBERS
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


    /* ==============================
       TEAM MEMBERS
    ============================== */

    initTeamMembers();

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
               Sembunyikan tombol submit.
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
               Nonaktifkan input.
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


/* =====================================================
   TEAM MEMBERS
   =====================================================
   
   FREE FIRE
   4 PEMAIN INTI + 1 CADANGAN = 5
   
   MOBILE LEGENDS
   5 PEMAIN INTI + 1 CADANGAN = 6
   
   ===================================================== */

function initTeamMembers() {


    /*
       Cari form Mobile Legends atau Free Fire.
    */

    const form =
        document.querySelector(
            '#mobileLegendsForm, #freeFireForm'
        );


    if (!form) {
        return;
    }


    /*
       Ambil daftar anggota.
    */

    const memberList =
        form.querySelector(
            '#memberList'
        );


    /*
       Ambil tombol tambah anggota.
    */

    const addMemberBtn =
        form.querySelector(
            '#addMemberBtn'
        );


    if (
        !memberList ||
        !addMemberBtn
    ) {
        return;
    }


    /*
       Cek jenis lomba.
    */

    const competition =
        form.dataset.competition;


    /* =================================================
       KONFIGURASI LOMBA
    ================================================= */

    const config = {


        /* =============================================
           MOBILE LEGENDS
           5 INTI + 1 CADANGAN
        ============================================= */

        mobile_legends: {

            maxPlayers: 6,

            corePlayers: 5,

            roles: [

                'EXP Lane',

                'Jungle',

                'Mid Lane',

                'Gold Lane',

                'Roam'

            ],

            extraLabel:
                'Hero Andalan',

            extraName:
                'hero',

            extraPlaceholder:
                'Contoh: Chou, Lunox',

            gameIdPlaceholder:
                'ID Mobile Legends',

            nicknamePlaceholder:
                'Nickname MLBB'

        },


        /* =============================================
           FREE FIRE
           4 INTI + 1 CADANGAN
        ============================================= */

        free_fire: {

            maxPlayers: 5,

            corePlayers: 4,

            roles: [

                'Rusher',

                'Support',

                'Sniper',

                'IGL'

            ],

            extraLabel:
                'Karakter Andalan',

            extraName:
                'character',

            extraPlaceholder:
                'Contoh: Alok, Hayato',

            gameIdPlaceholder:
                'ID Free Fire',

            nicknamePlaceholder:
                'Nickname Free Fire'

        }

    };


    /*
       Ambil konfigurasi sesuai lomba.
    */

    const settings =
        config[competition];


    if (!settings) {
        return;
    }


    /*
       Hitung anggota yang sudah ada.
    */

    let memberCount =
        memberList.querySelectorAll(
            '[data-member]'
        ).length;


    /* =================================================
       BUAT CARD ANGGOTA
    ================================================= */

    function createMemberCard(
        playerNumber
    ) {


        /*
           Tentukan pemain cadangan.
        */

        const isReserve =
            playerNumber >
            settings.corePlayers;


        /*
           Anggota pertama = Kapten.
        */

        const isCaptain =
            playerNumber === 1;


        /*
           Judul anggota.
        */

        let title =
            `Anggota ${playerNumber}`;


        if (isCaptain) {

            title +=
                ' (Kapten)';

        }

        else if (isReserve) {

            title +=
                ' (Cadangan)';

        }

        else {

            title +=
                ' (Pemain Inti)';

        }


        /*
           Buat pilihan role.
        */

        const roleOptions =
            settings.roles
                .map(function (role) {

                    return `
                        <option value="${role}">
                            ${role}
                        </option>
                    `;

                })
                .join('');


        /*
           Buat card.
        */

        const card =
            document.createElement(
                'div'
            );


        card.className =
            'member-card';


        card.setAttribute(
            'data-member',
            ''
        );


        card.setAttribute(
            'data-player',
            playerNumber
        );


        /* =================================================
           ISI CARD
        ================================================= */

        card.innerHTML = `

            <div class="member-title">

                ${title}

            </div>


            <div class="member-grid">


                <!-- NAMA -->

                <div class="form-group">

                    <label>

                        Nama Lengkap

                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        name="player${playerNumber}_name"
                        required
                        placeholder="Nama pemain"
                    >

                </div>


                <!-- NIS -->

                <div class="form-group">

                    <label>

                        NIS / NISN

                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        name="player${playerNumber}_student_id"
                        required
                        placeholder="NIS / NISN"
                    >

                </div>


                <!-- GAME ID -->

                <div class="form-group">

                    <label>

                        Game ID

                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        name="player${playerNumber}_game_id"
                        required
                        placeholder="${settings.gameIdPlaceholder}"
                    >

                </div>


                <!-- NICKNAME -->

                <div class="form-group">

                    <label>

                        Nickname

                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        name="player${playerNumber}_nickname"
                        required
                        placeholder="${settings.nicknamePlaceholder}"
                    >

                </div>


                <!-- ROLE -->

                <div class="form-group">

                    <label>

                        Role

                        <span>*</span>

                    </label>


                    <select
                        name="player${playerNumber}_role"
                        required
                    >

                        <option value="">
                            Pilih role
                        </option>

                        ${roleOptions}

                    </select>

                </div>


                <!-- HERO / KARAKTER -->

                <div class="form-group">

                    <label>

                        ${settings.extraLabel}

                    </label>


                    <input
                        type="text"
                        name="player${playerNumber}_${settings.extraName}"
                        placeholder="${settings.extraPlaceholder}"
                    >

                </div>


            </div>

        `;


        return card;

    }


    /* =================================================
       UPDATE TOMBOL
    ================================================= */

    function updateButton() {


        const currentPlayers =
            memberList.querySelectorAll(
                '[data-member]'
            ).length;


        const remaining =
            settings.maxPlayers -
            currentPlayers;


        /*
           Jika sudah maksimal.
        */

        if (
            currentPlayers >=
            settings.maxPlayers
        ) {

            addMemberBtn.disabled =
                true;


            addMemberBtn.textContent =
                `✓ Maksimal ${settings.maxPlayers} Anggota`;


            return;

        }


        /*
           Tombol masih bisa digunakan.
        */

        addMemberBtn.disabled =
            false;


        addMemberBtn.textContent =
            `+ Tambah Anggota (${remaining} slot tersisa)`;

    }


    /* =================================================
       TOMBOL TAMBAH ANGGOTA
    ================================================= */

    addMemberBtn.addEventListener(
        'click',
        function () {


            const currentPlayers =
                memberList.querySelectorAll(
                    '[data-member]'
                ).length;


            /*
               Cegah lebih dari maksimal.
            */

            if (
                currentPlayers >=
                settings.maxPlayers
            ) {

                updateButton();

                return;

            }


            /*
               Nomor anggota berikutnya.
            */

            memberCount =
                currentPlayers + 1;


            /*
               Buat anggota.
            */

            const newMember =
                createMemberCard(
                    memberCount
                );


            /*
               Masukkan ke member list.
            */

            memberList.appendChild(
                newMember
            );


            /*
               Update tombol.
            */

            updateButton();


            /*
               Scroll ke anggota baru.
            */

            newMember.scrollIntoView({

                behavior: 'smooth',

                block: 'center'

            });

        }
    );


    /*
       Update tombol saat halaman dibuka.
    */

    updateButton();

}