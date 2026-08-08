// Free Fire - Tambah anggota squad
let memberCount = 1;

function addMember() {
    memberCount++;
    const n = memberCount;
    const card = document.createElement('div');
    card.className = 'member-card';
    card.innerHTML = `
        <div class="member-title">Anggota ${n}</div>
        <div class="member-grid">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" name="player${n}_name" placeholder="Nama pemain"></div>
            <div class="form-group"><label>Game ID</label><input type="text" name="player${n}_game_id" placeholder="ID FF"></div>
            <div class="form-group"><label>Nickname</label><input type="text" name="player${n}_nickname" placeholder="Nickname FF"></div>
            <div class="form-group"><label>Role</label><select name="player${n}_role"><option value="">Pilih role</option><option value="Rusher">Rusher</option><option value="Support">Support</option><option value="Sniper">Sniper</option><option value="IGL">IGL</option></select></div>
            <div class="form-group"><label>Karakter Andalan</label><input type="text" name="player${n}_character" placeholder="Contoh: Alok, Hayato"></div>
        </div>
        <button type="button" class="remove-member" onclick="this.closest('.member-card').remove()">Hapus Anggota</button>
    `;
    document.getElementById('memberList').appendChild(card);
}
