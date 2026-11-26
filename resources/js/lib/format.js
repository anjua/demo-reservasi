const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

export function rupiah(value) {
    return 'Rp' + Number(value || 0).toLocaleString('id-ID');
}

/** "2026-11-26" -> Date lokal (tanpa geser zona waktu) */
export function parseDate(str) {
    const [y, m, d] = String(str).slice(0, 10).split('-').map(Number);
    return new Date(y, m - 1, d);
}

/** Kamis, 26 November 2026 */
export function tanggalPanjang(str) {
    const d = parseDate(str);
    return `${HARI[d.getDay()]}, ${d.getDate()} ${BULAN[d.getMonth()]} ${d.getFullYear()}`;
}

/** 26 Nov 2026 */
export function tanggalPendek(str) {
    const d = parseDate(str);
    return `${d.getDate()} ${BULAN[d.getMonth()].slice(0, 3)} ${d.getFullYear()}`;
}

export function namaHari(str, short = false) {
    const h = HARI[parseDate(str).getDay()];
    return short ? h.slice(0, 3) : h;
}

export function durasi(menit) {
    const j = Math.floor(menit / 60);
    const m = menit % 60;
    if (j && m) return `${j} jam ${m} mnt`;
    if (j) return `${j} jam`;
    return `${m} menit`;
}

export function jam(t) {
    return String(t).slice(0, 5).replace(':', '.');
}

/** Tautan WhatsApp dengan pesan otomatis */
export function waLink(phone, text = '') {
    const digits = String(phone || '').replace(/\D/g, '').replace(/^0/, '62');
    return `https://wa.me/${digits}${text ? '?text=' + encodeURIComponent(text) : ''}`;
}

export function todayStr() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}
