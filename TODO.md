# TODO - STC 2026 Competition Registration Forms (Phase 2)

## Tujuan
Memperbaiki semua form pendaftaran lomba agar:
- Menggunakan proper `name=""` attributes di semua input
- Menggunakan `data-submit` + `data-competition` attributes
- Tidak menggunakan `alert()` atau `onsubmit` inline
- Mengirim data via `FormData` + `fetch()` ke backend API
- Menambahkan CSS submission.css dan payment.css

## Status

### Individual Competitions (Done)
- [x] **Web Design** — `data-competition="web_design"`
- [x] **Infografis** — `data-competition="infografis"`
- [x] **Microsoft Excel** — `data-competition="excel"`
- [x] **Speed Typing** — `data-competition="speed_typing"`

### Team Competitions (Done)
- [x] **Cerdas Cermat** — `data-competition="cerdas_cermat"` (fixed 3 members)
- [x] **Mobile Legends** — `data-competition="mobile_legends"` (dynamic members with sequential naming)
- [x] **Free Fire** — `data-competition="free_fire"` (dynamic members with sequential naming)

### Supporting Files (Created)
- [x] `page_Competition/js/mobile-legends.js` — Updated with sequential player naming
- [x] `page_Competition/js/freefire.js` — Updated with sequential player naming
- [x] `JavaScript/form-common.js` — Common form utilities
- [x] `JavaScript/submission.js` — Form submission handler using fetch()

### Landing Page Fix (Done)
- [x] **Landing_page/Index.html** — Fixed corrupted file with unresolved Git merge conflict markers (`<<<<<<<`, `=======`, `>>>>>>>`) that caused the entire page to render broken/messy. Resolved all conflicts (kept the complete "Updated upstream" version), fixed CSS link to `/desain/style.css`, and restored all sections (Hero sky theme, About, Sponsors, Competitions with working "Daftar Sekarang" links, Prize, Timeline, CTA, Footer).

### Remaining Tasks
- [ ] Ensure backend API endpoints match the competition slugs
  - web_design, infografis, excel, speed_typing, cerdas_cermat, mobile_legends, free_fire
- [ ] Test all 7 forms with actual API integration

