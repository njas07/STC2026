/* =====================================================
   STC 2026 - Menyisipkan bagian umum formulir lomba
   (Kartu Pelajar, Follow Instagram, Pembayaran,
    Persetujuan, Tombol Submit, Hasil)
   Dipakai otomatis pada form[data-submit]
   ===================================================== */

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-submit]').forEach(function (form) {
        injectCommonFields(form);
    });
});

function injectCommonFields(form) {
    const competition = form.getAttribute('data-competition') || 'umum';

    const html = `
        <!-- ===== KARTU PELAJAR ===== -->
        <div class="form-section">
            <h4>Kartu Pelajar</h4>
            <p>Diperbolehkan format <strong>JPG, PNG, WEBP</strong>. Maksimal ukuran <strong>2MB</strong>.</p>
            <div class="upload-group">
                <input type="file" name="student_card" accept="image/*" class="file-input" data-preview="previewStudentCard">
                <p class="file-info">Format: JPG/PNG/WEBP · Maksimal 2MB</p>
                <img class="preview" id="previewStudentCard" alt="Pratinjau Kartu Pelajar">
            </div>
        </div>

        <!-- ===== FOLLOW INSTAGRAM ===== -->
        <div class="form-section">
            <h4>Follow Instagram (Wajib)</h4>
            <p>Unggah bukti screenshot follow.</p>
            <div class="form-grid">
                <div class="form-group">
                    <label>Username Instagram Sekolah <span>*</span></label>
                    <input type="text" name="instagram_school" placeholder="username_ig_sekolah" required>
                </div>
                <div class="upload-group">
                    <label>Bukti Follow IG Sekolah <span>*</span></label>
                    <input type="file" name="instagram_school_proof" accept="image/*" class="file-input" data-preview="previewIgSchool" required>
                    <img class="preview" id="previewIgSchool" alt="Bukti IG Sekolah">
                </div>
                <div class="form-group">
                    <label>Username Instagram STC <span>*</span></label>
                    <input type="text" name="instagram_stc" placeholder="username_ig_stc" required>
                </div>
                <div class="upload-group">
                    <label>Bukti Follow IG STC <span>*</span></label>
                    <input type="file" name="instagram_stc_proof" accept="image/*" class="file-input" data-preview="previewIgStc" required>
                    <img class="preview" id="previewIgStc" alt="Bukti IG STC">
                </div>
            </div>
        </div>

        <!-- ===== PEMBAYARAN ===== -->
        <div class="form-section payment-section">
            <h4>Data Pembayaran</h4>
            <p>Isi data dan unggah bukti pembayaran lomba.</p>
            <div class="payment-grid">
                <div class="form-group">
                    <label>Metode Pembayaran <span>*</span></label>
                    <select name="payment_method" required>
                        <option value="">Pilih metode</option>
                        <option value="transfer_bank">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="dana">DANA</option>
                        <option value="ovo">OVO</option>
                        <option value="gopay">GoPay</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Bayar <span>*</span></label>
                    <input type="text" name="amount" placeholder="Contoh: 50000" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Pembayaran <span>*</span></label>
                    <input type="date" name="transaction_date" required>
                </div>
                <div class="upload-group">
                    <label>Upload Bukti Pembayaran <span>*</span></label>
                    <input type="file" name="payment_proof" accept="image/*" class="file-input" data-preview="previewPay" required>
                    <img class="preview" id="previewPay" alt="Bukti Pembayaran">
                </div>
            </div>
        </div>

        <!-- ===== PERSETUJUAN ===== -->
        <div class="agreement">
            <div class="agree-item">
                <input type="checkbox" required>
                <label>Data yang saya isi sudah benar dan dapat dipertanggungjawabkan.</label>
            </div>
            <div class="agree-item">
                <input type="checkbox" required>
                <label>Saya menyetujui seluruh ketentuan lomba.</label>
            </div>
            <div class="agree-item">
                <input type="checkbox" required>
                <label>Saya sudah melakukan pembayaran.</label>
            </div>
            <div class="agree-item">
                <input type="checkbox" required>
                <label>Saya sudah mengikuti Instagram yang diwajibkan.</label>
            </div>
        </div>

        <button type="submit" class="btn-submit">SUBMIT PENDAFTARAN ☁</button>
        <p class="note">* Semua data wajib diisi. Setelah submit, Anda akan mendapat kode pendaftaran.</p>

        <div class="submit-result" id="submitResult">
            <span class="icon">✅</span>
            <h3>PENDAFTARAN BERHASIL</h3>
            <div>Kode Pendaftaran:</div>
            <div class="code-display" id="resultCode">-</div>
            <p>Status: MENUNGGU VERIFIKASI</p>
        </div>
    `;

    form.insertAdjacentHTML('beforeend', html);
}
