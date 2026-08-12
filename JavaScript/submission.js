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

   FIELD ANGGOTA:
   - Nama Lengkap
   - NIS / NISN
   - Game ID
   - Nickname

   TIDAK ADA:
   - Role
   - Hero Andalan
   - Karakter Andalan
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
           5 INTI + 1 CADANGAN = 6
        ============================================= */

        mobile_legends: {

            maxPlayers: 6,

            corePlayers: 5,

            gameIdPlaceholder:
                'ID Mobile Legends',

            nicknamePlaceholder:
                'Nickname MLBB'

        },


        /* =============================================
           FREE FIRE
           4 INTI + 1 CADANGAN = 5
        ============================================= */

        free_fire: {

            maxPlayers: 5,

            corePlayers: 4,

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
           Tentukan apakah cadangan.
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
           Buat card anggota.
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
           HTML ANGGOTA

           SAMA UNTUK:
           - MOBILE LEGENDS
           - FREE FIRE

           HANYA 4 FIELD
        ================================================= */

        card.innerHTML = `

            <div class="member-title">

                ${title}

            </div>


            <div class="member-grid">


                <!-- =====================================
                     NAMA LENGKAP
                ====================================== -->

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



                <!-- =====================================
                     NIS / NISN
                ====================================== -->

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



                <!-- =====================================
                     GAME ID
                ====================================== -->

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



                <!-- =====================================
                     NICKNAME
                ====================================== -->

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


            </div>

        `;


        return card;

    }



    /* =================================================
       UPDATE TOMBOL TAMBAH ANGGOTA
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
           Sudah mencapai batas maksimal.
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
           Masih bisa menambah anggota.
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


            /*
               Hitung anggota saat ini.
            */

            const currentPlayers =
                memberList.querySelectorAll(
                    '[data-member]'
                ).length;



            /*
               Jangan melewati batas.
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
               Buat card anggota baru.
            */

            const newMember =
                createMemberCard(
                    memberCount
                );



            /*
               Masukkan card ke daftar.
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



    /* =================================================
       INITIAL BUTTON
    ================================================= */

    updateButton();

}

/* =========================================================
   TEAM MEMBER SYSTEM
   STC 2026
   ---------------------------------------------------------
   MOBILE LEGENDS
   5 PEMAIN INTI + 1 CADANGAN = 6

   FREE FIRE
   4 PEMAIN INTI + 1 CADANGAN = 5
========================================================= */

function initTeamMembers() {

    /* =====================================================
       CARI FORM
    ===================================================== */

    const forms = document.querySelectorAll(
        '#mobileLegendsForm, #freeFireForm'
    );

    if (!forms.length) {
        return;
    }


    forms.forEach(function (form) {

        const memberList = form.querySelector('#memberList');

        if (!memberList) {
            return;
        }


        /* =================================================
           CEK JENIS LOMBA
        ================================================= */

        const competition =
            form.dataset.competition;


        /* =================================================
           KONFIGURASI
        ================================================= */

        const config = {

            mobile_legends: {
                maxPlayers: 6,
                corePlayers: 5,

                gameIdPlaceholder:
                    'ID Mobile Legends',

                nicknamePlaceholder:
                    'Nickname Mobile Legends',

                memberLabel:
                    'Daftar Anggota Tim (5 Inti + Maks. 1 Cadangan)'
            },


            free_fire: {
                maxPlayers: 5,
                corePlayers: 4,

                gameIdPlaceholder:
                    'ID Free Fire',

                nicknamePlaceholder:
                    'Nickname Free Fire',

                memberLabel:
                    'Daftar Anggota Squad (4 Inti + Maks. 1 Cadangan)'
            }

        };


        const settings = config[competition];


        if (!settings) {
            return;
        }


        /* =================================================
           CARI SEMUA TOMBOL TAMBAH
           -------------------------------------------------
           Kita hapus SEMUA tombol duplikat terlebih dahulu.
        ================================================= */

        const allAddButtons =
            form.querySelectorAll('.add-member');


        let addMemberBtn = null;


        allAddButtons.forEach(function (button, index) {

            if (index === 0) {

                /*
                   Tombol pertama dipertahankan.
                */

                addMemberBtn = button;

            } else {

                /*
                   Tombol berikutnya adalah duplikat.
                */

                button.remove();

            }

        });


        /* =================================================
           JIKA TOMBOL BELUM ADA
        ================================================= */

        if (!addMemberBtn) {

            addMemberBtn =
                document.createElement('button');

            addMemberBtn.type = 'button';

            addMemberBtn.className =
                'add-member';

            addMemberBtn.id =
                'addMemberBtn';

            /*
               Masukkan setelah memberList.
            */

            memberList.after(addMemberBtn);
        }


        /*
           Pastikan ID benar.
        */

        addMemberBtn.id =
            'addMemberBtn';


        /* =================================================
           PINDAHKAN TOMBOL KE TEMPAT YANG BENAR
           -------------------------------------------------
           Ini penting untuk mengatasi tombol FF yang
           keluar dari card / muncul di bawah card.
        ================================================= */

        const teamSection =
            memberList.closest('.team-section');


        if (teamSection) {

            /*
               Tombol harus menjadi anak langsung
               dari team-section.
            */

            teamSection.appendChild(addMemberBtn);
        }


        /* =================================================
           HITUNG MEMBER YANG SUDAH ADA
        ================================================= */

        let memberCount =
            memberList.querySelectorAll(
                '[data-member]'
            ).length;


        /*
           Jika belum ada member,
           buat Anggota 1.
        */

        if (memberCount === 0) {

            createMember(
                form,
                memberList,
                settings,
                1,
                true
            );

            memberCount = 1;
        }


        /* =================================================
           UPDATE TOMBOL
        ================================================= */

        updateAddButton(
            addMemberBtn,
            memberCount,
            settings.maxPlayers
        );


        /* =================================================
           HINDARI EVENT LISTENER DOBEL
        ================================================= */

        addMemberBtn.onclick = function () {

            const currentMembers =
                memberList.querySelectorAll(
                    '[data-member]'
                ).length;


            /*
               Sudah penuh
            */

            if (
                currentMembers >=
                settings.maxPlayers
            ) {

                return;
            }


            const nextNumber =
                currentMembers + 1;


            /*
               Buat member baru
            */

            createMember(
                form,
                memberList,
                settings,
                nextNumber,
                false
            );


            /*
               Update jumlah member
            */

            const newCount =
                memberList.querySelectorAll(
                    '[data-member]'
                ).length;


            updateAddButton(
                addMemberBtn,
                newCount,
                settings.maxPlayers
            );
        };

    });
}


/* =========================================================
   CREATE MEMBER
========================================================= */

function createMember(
    form,
    memberList,
    settings,
    number,
    isCaptain
) {

    const memberCard =
        document.createElement('div');


    memberCard.className =
        'member-card';


    memberCard.dataset.member =
        '';


    /*
       Nama member
    */

    const title =
        isCaptain
            ? `Anggota ${number} (Kapten)`
            : `Anggota ${number}`;


    memberCard.innerHTML = `

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
                    name="player${number}_name"
                    required
                    placeholder="Nama pemain"
                >

            </div>


            <!-- NIS / NISN -->

            <div class="form-group">

                <label>
                    NIS / NISN
                    <span>*</span>
                </label>

                <input
                    type="text"
                    name="player${number}_student_id"
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
                    name="player${number}_game_id"
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
                    name="player${number}_nickname"
                    required
                    placeholder="${settings.nicknamePlaceholder}"
                >

            </div>

        </div>
    `;


    /*
       Masukkan member ke list
    */

    memberList.appendChild(memberCard);
}


/* =========================================================
   UPDATE TOMBOL
========================================================= */

function updateAddButton(
    button,
    currentCount,
    maxPlayers
) {

    const remaining =
        maxPlayers - currentCount;


    if (remaining <= 0) {

        button.textContent =
            '✓ Slot Sudah Penuh';

        button.disabled = true;

        return;
    }


    button.disabled = false;


    button.textContent =
        `+ Tambah Anggota (${remaining} slot tersisa)`;
}


/* =========================================================
   AUTO INIT
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        initTeamMembers();

    }
);