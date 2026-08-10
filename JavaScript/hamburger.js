/* =========================================================
   STC 2026
   HAMBURGER MENU
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const hamburger = document.getElementById("hamburger");
    const navMenu = document.getElementById("navMenu");
    const dropdown = document.querySelector(".dropdown");

    // Jika elemen tidak ditemukan, hentikan script
    if (!hamburger || !navMenu) {
        return;
    }


    /* =====================================================
       HAMBURGER TOGGLE
    ===================================================== */

    hamburger.addEventListener("click", function (event) {

        event.stopPropagation();

        hamburger.classList.toggle("active");
        navMenu.classList.toggle("active");

        const isOpen = navMenu.classList.contains("active");

        hamburger.setAttribute(
            "aria-expanded",
            isOpen ? "true" : "false"
        );

    });


    /* =====================================================
       NAVIGATION LINK
       Tutup menu setelah link diklik
    ===================================================== */

    const navLinks = navMenu.querySelectorAll(".nav-link");

    navLinks.forEach(function (link) {

        link.addEventListener("click", function () {

            // Dropdown tidak langsung menutup di mobile
            if (
                link.closest(".dropdown") &&
                window.innerWidth <= 900
            ) {
                return;
            }

            closeMenu();

        });

    });


    /* =====================================================
       DROPDOWN MOBILE
    ===================================================== */

    if (dropdown) {

        const dropdownLink =
            dropdown.querySelector(".nav-link");

        if (dropdownLink) {

            dropdownLink.addEventListener(
                "click",
                function (event) {

                    if (window.innerWidth <= 900) {

                        event.preventDefault();
                        event.stopPropagation();

                        dropdown.classList.toggle("active");

                    }

                }
            );

        }

    }


    /* =====================================================
       KLIK DI LUAR MENU
    ===================================================== */

    document.addEventListener("click", function (event) {

        if (
            !navMenu.contains(event.target) &&
            !hamburger.contains(event.target)
        ) {

            closeMenu();

        }

    });


    /* =====================================================
       ESCAPE KEY
    ===================================================== */

    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {

            closeMenu();

        }

    });


    /* =====================================================
       RESPONSIVE
       Tutup menu ketika kembali ke desktop
    ===================================================== */

    window.addEventListener("resize", function () {

        if (window.innerWidth > 900) {

            closeMenu();

        }

    });


    /* =====================================================
       FUNCTION CLOSE MENU
    ===================================================== */

    function closeMenu() {

        hamburger.classList.remove("active");
        navMenu.classList.remove("active");

        hamburger.setAttribute(
            "aria-expanded",
            "false"
        );

        if (dropdown) {
            dropdown.classList.remove("active");
        }

    }

});