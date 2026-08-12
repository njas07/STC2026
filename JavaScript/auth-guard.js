/* =========================================================
   STC 2026 — PENJAGA HALAMAN

   localStorage bisa diubah siapa saja lewat console browser:

       localStorage.setItem('stc_user', '{"role":"admin"}')

   Jadi localStorage TIDAK boleh dipakai sebagai penentu hak
   akses. File ini menanyakan ke server siapa yang sedang
   login, berdasarkan session PHP + tabel `users` di database.

   Pemakaian di halaman:

       <script src="JavaScript/auth-guard.js"></script>
       <script>
         STCGuard.requireAdmin().then(function (user) { ... });
       </script>
   ========================================================= */

window.STCGuard = (function () {

    /* Ubah kalau backend dipasang di alamat lain. */
    var BACKEND = 'http://127.0.0.1/stc2026-backend';

    var LOGIN_PAGE = 'multi_page/Login.html';

    function loginUrl() {
        /* Halaman di dalam subfolder perlu naik satu tingkat. */
        var dalamSubfolder =
            location.pathname.indexOf('/multi_page/') !== -1 ||
            location.pathname.indexOf('/page_Competition/') !== -1;

        return (dalamSubfolder ? '../' : '') + LOGIN_PAGE;
    }

    function tendang(pesan) {
        try {
            localStorage.removeItem('stc_user');
        } catch (e) { /* diabaikan */ }

        if (pesan) {
            alert(pesan);
        }

        location.replace(loginUrl());
    }

    /* -----------------------------------------------------
       Tanya server: siapa yang sedang login?
       ----------------------------------------------------- */
    function ambilUser(butuhAdmin) {

        var url = BACKEND + '/auth/me.php' + (butuhAdmin ? '?admin=1' : '');

        return fetch(url, {
            method: 'GET',
            credentials: 'include',
            cache: 'no-store'
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { status: res.status, data: data };
                });
            })
            .then(function (hasil) {

                if (hasil.status === 401) {
                    tendang(null);
                    return Promise.reject(new Error('belum login'));
                }

                if (hasil.status === 403) {
                    tendang('Halaman ini hanya untuk admin.');
                    return Promise.reject(new Error('bukan admin'));
                }

                if (!hasil.data || !hasil.data.success) {
                    tendang(null);
                    return Promise.reject(new Error('sesi tidak valid'));
                }

                var user = hasil.data.user;

                /* Disimpan hanya untuk ditampilkan (nama, email),
                   bukan sebagai dasar hak akses. */
                try {
                    localStorage.setItem('stc_user', JSON.stringify(user));
                } catch (e) { /* diabaikan */ }

                return user;
            })
            .catch(function (err) {

                if (err && /belum login|bukan admin|sesi/.test(err.message)) {
                    return Promise.reject(err);
                }

                /* Server tidak bisa dihubungi — jangan diloloskan. */
                document.body.innerHTML =
                    '<div style="font-family:Poppins,sans-serif;padding:60px 24px;' +
                    'text-align:center;color:#1f3e6c">' +
                    '<h2 style="margin-bottom:10px">Tidak bisa menghubungi server</h2>' +
                    '<p style="color:#4a5a76">Pastikan backend STC 2026 berjalan, ' +
                    'lalu muat ulang halaman ini.</p></div>';

                return Promise.reject(err);
            });
    }

    return {
        BACKEND: BACKEND,
        requireLogin: function () { return ambilUser(false); },
        requireAdmin: function () { return ambilUser(true); },

        logout: function () {
            fetch(BACKEND + '/auth/logout.php', {
                method: 'GET',
                credentials: 'include'
            }).finally(function () {
                try {
                    localStorage.removeItem('stc_user');
                } catch (e) { /* diabaikan */ }
                location.replace(loginUrl());
            });
        }
    };

})();
