// Mobile Legends - Tambah anggota tim
let memberCount = 1;

function addMember() {
    memberCount++;
    const card = document.createElement('div');
    card.className = 'member-card';
    card.dataset.member = true;
    card.innerHTML = `
        <div class="member-title">Anggota ${memberCount - 5 > 0 ? memberCount - 5 : memberCount}</div>
        <div class="member-grid">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" placeholder="Nama pemain">
            </div>
            <div class="form-group">
                <label>ID Game / Nickname</label>
                <input type="text" placeholder="Nickname MLBB">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select>
                    <option value="">Pilih role</option>
                    <option>EXP Lane</option>
                    <option>Jungle</option>
                    <option>Mid Lane</option>
                    <option>Gold Lane</option>
                    <option>Roam</option>
                </select>
            </div>
            <div class="form-group">
                <label>Hero Andalan</label>
                <input type="text" placeholder="Contoh: Chou, Lunox">
            </div>
        </div>
        <button type="button" class="remove-member" onclick="this.closest('.member-card').remove()">Hapus Anggota</button>
    `;
    document.getElementById('memberList').appendChild(card);
}

