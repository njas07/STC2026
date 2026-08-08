// Mobile Legends - Tambah anggota tim
let memberCount = 1;

function addMember() {
    memberCount++;
    const n = memberCount;
    const card = document.createElement('div');
    card.className = 'member-card';
    card.dataset.member = true;
    card.innerHTML = `
        <div class="member-title">Anggota ${n}</div>
        <div class="member-grid">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" name="player${n}_name" placeholder="Nama pemain"></div>
            <div class="form-group"><label>Game ID</label><input type="text" name="player${n}_game_id" placeholder="ID MLBB"></div>
            <div class="form-group"><label>Nickname</label><input type="text" name="player${n}_nickname" placeholder="Nickname MLBB"></div>
            <div class="form-group"><label>Role</label><select name="player${n}_role"><option value="">Pilih role</option><option value="EXP Lane">EXP Lane</option><option value="Jungle">Jungle</option><option value="Mid Lane">Mid Lane</option><option value="Gold Lane">Gold Lane</option><option value="Roam">Roam</option></select></div>
            <div class="form-group"><label>Hero Andalan</label><input type="text" name="player${n}_hero" placeholder="Contoh: Chou, Lunox"></div>
        </div>
        <button type="button" class="remove-member" onclick="this.closest('.member-card').remove()">Hapus Anggota</button>
    `;
    document.getElementById('memberList').appendChild(card);
}
