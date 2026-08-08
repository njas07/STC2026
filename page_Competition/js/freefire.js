// Free Fire - Tambah anggota squad
let memberCount = 1;

function addMember() {
    memberCount++;
    const card = document.createElement('div');
    card.className = 'member-card';
    card.innerHTML = `
        <div class="member-title">Anggota ${memberCount - 4 > 0 ? memberCount - 4 : memberCount}</div>
        <div class="member-grid">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" placeholder="Nama pemain"></div>
            <div class="form-group"><label>ID Game / Nickname</label><input type="text" placeholder="Nickname FF"></div>
            <div class="form-group"><label>Role</label><select><option value="">Pilih role</option><option>Rusher</option><option>Support</option><option>Sniper</option><option>IGL (In Game Leader)</option></select></div>
            <div class="form-group"><label>Karakter Andalan</label><input type="text" placeholder="Contoh: Alok, Hayato"></div>
        </div>
        <button type="button" class="remove-member" onclick="this.closest('.member-card').remove()">Hapus Anggota</button>
    `;
    document.getElementById('memberList').appendChild(card);
}

