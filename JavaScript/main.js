/*========================================
 STC LANDING PAGE - MAIN JS
========================================*/

document.addEventListener('DOMContentLoaded', function () {

/*==============================
  PRELOADER
==============================*/

const preloader = document.getElementById('preloader');

if (preloader) {

    // Beri browser waktu untuk menampilkan
    // animasi preloader terlebih dahulu.
    requestAnimationFrame(() => {

        setTimeout(() => {

            preloader.classList.add('hidden');

            // Hapus setelah fade-out selesai
            setTimeout(() => {

                if (preloader) {
                    preloader.remove();
                }

            }, 700);

        }, 1400);

    });

}

  /*==============================
   COMPETITION FILTER
  ==============================*/
  const filterButtons = document.querySelectorAll('.filter-btn');
  const competitionCards = document.querySelectorAll('.competition-card');

  filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      filterButtons.forEach(function (btn) {
        btn.classList.remove('active');
      });
      button.classList.add('active');

      const filter = button.dataset.filter;

      competitionCards.forEach(function (card) {
        if (filter === 'all' || card.dataset.category === filter) {
          card.style.display = 'grid';
          card.classList.add('revealed');
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  /*==============================
   SCROLL REVEAL
  ==============================*/
  const revealEls = document.querySelectorAll('.reveal');

  if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver(function (entries, observer) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    revealEls.forEach(function (el) {
      revealObserver.observe(el);
    });
  } else {
    revealEls.forEach(function (el) {
      el.classList.add('revealed');
    });
  }

  /*==============================
   COUNTER ANIMATION
  ==============================*/
  const counters = document.querySelectorAll('.counter');

  function animateCounter(el) {
    const target = parseInt(el.getAttribute('data-target'), 10);
    const suffix = el.getAttribute('data-suffix') || '';
    const duration = 2000;
    const start = 0;
    const startTime = performance.now();

    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const value = Math.floor(eased * (target - start) + start);
      el.textContent = value + suffix;
      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        el.textContent = target + suffix;
      }
    }
    requestAnimationFrame(update);
  }

  if ('IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver(function (entries, observer) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counters.forEach(function (el) {
      counterObserver.observe(el);
    });
  } else {
    counters.forEach(function (el) {
      el.textContent = el.getAttribute('data-target') + (el.getAttribute('data-suffix') || '');
    });
  }

  /*==============================
   COUNTDOWN TIMER
  ==============================*/
  const countdownEl = document.getElementById('countdown');
  if (countdownEl) {
    const targetDate = new Date('2026-06-01T08:00:00').getTime();

    function updateCountdown() {
      const now = new Date().getTime();
      const distance = targetDate - now;

      if (distance < 0) {
        countdownEl.innerHTML = '<div class="countdown-msg">Competition Started!</div>';
        return;
      }

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      countdownEl.innerHTML =
        '<div class="cd-box"><span class="cd-num">' + days + '</span><span class="cd-label">Days</span></div>' +
        '<div class="cd-sep">:</div>' +
        '<div class="cd-box"><span class="cd-num">' + hours + '</span><span class="cd-label">Hours</span></div>' +
        '<div class="cd-sep">:</div>' +
        '<div class="cd-box"><span class="cd-num">' + minutes + '</span><span class="cd-label">Minutes</span></div>' +
        '<div class="cd-sep">:</div>' +
        '<div class="cd-box"><span class="cd-num">' + seconds + '</span><span class="cd-label">Seconds</span></div>';
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
  }

  /*==============================
   NEWSLETTER FORM
  ==============================*/
  const newsletterForm = document.querySelector('.newsletter-form');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const input = newsletterForm.querySelector('input');
      if (input && input.value.trim() !== '') {
        alert('Terima kasih! Anda berhasil berlangganan ✨');
        input.value = '';
      } else {
        alert('Silakan masukkan email Anda terlebih dahulu.');
      }
    });
  }

});
