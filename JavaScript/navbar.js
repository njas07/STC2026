/* =========================================================
   STC 2026 - NAVBAR JAVASCRIPT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       ELEMENT
    ===================================================== */

    const header = document.querySelector(".header");
    const hamburger = document.querySelector(".hamburger");
    const navMenu = document.querySelector(".nav-menu");
    const dropdown = document.querySelector(".dropdown");


    /* =====================================================
       HEADER SCROLL
    ===================================================== */

    if (header) {

        window.addEventListener("scroll", function () {

            header.classList.toggle(
                "scrolled",
                window.scrollY > 40
            );

        });

    }


    /* =====================================================
       HAMBURGER MENU
    ===================================================== */

    if (hamburger && navMenu) {

        hamburger.addEventListener("click", function (e) {

            e.stopPropagation();

            hamburger.classList.toggle("active");

            navMenu.classList.toggle("active");

        });


        /* =================================================
           CLOSE MENU AFTER CLICK LINK
        ================================================= */

        const navLinks =
            navMenu.querySelectorAll(".nav-link");

        navLinks.forEach(function (link) {

            link.addEventListener("click", function () {

                if (window.innerWidth <= 900) {

                    hamburger.classList.remove("active");

                    navMenu.classList.remove("active");

                }

            });

        });


        /* =================================================
           CLOSE MENU WHEN CLICK OUTSIDE
        ================================================= */

        document.addEventListener("click", function (e) {

            if (
                window.innerWidth <= 900 &&
                !navMenu.contains(e.target) &&
                !hamburger.contains(e.target)
            ) {

                hamburger.classList.remove("active");

                navMenu.classList.remove("active");

            }

        });

    }


    /* =====================================================
       DROPDOWN MOBILE
    ===================================================== */

    if (dropdown) {

        const dropdownLink =
            dropdown.querySelector(":scope > .nav-link");

        if (dropdownLink) {

            dropdownLink.addEventListener(
                "click",
                function (e) {

                    if (window.innerWidth <= 900) {

                        e.preventDefault();

                        dropdown.classList.toggle("active");

                    }

                }
            );

        }

    }

});