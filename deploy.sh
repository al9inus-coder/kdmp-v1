#!/usr/bin/env bash
#
# Deploy KDMP ke server produksi.
#
#   cd /var/www/kdmp && ./deploy.sh
#
# Menjalankan urutan yang gampang terlupa setelah pull, dan yang gejalanya
# beda-beda kalau terlewat:
#   - migrasi belum jalan   -> halaman tertentu 500, halaman lain normal
#   - aset belum di-build   -> tampilan berantakan tanpa galat apa pun
#   - cache belum dibersihkan -> perubahan seolah tidak masuk
#
# Berhenti pada kesalahan pertama. Lebih baik gagal separuh jalan dengan
# pesan jelas daripada lanjut diam-diam di atas keadaan yang tidak jelas.
set -euo pipefail

CABANG="main"

info()  { printf '\n\033[1;34m==>\033[0m %s\n' "$1"; }
sukses(){ printf '\033[1;32m  ok\033[0m %s\n' "$1"; }
gagal() { printf '\n\033[1;31mGAGAL:\033[0m %s\n\n' "$1" >&2; exit 1; }

cd "$(dirname "$0")"

# --- Pemeriksaan awal ----------------------------------------------------
# Server deploy harus bersih dan ada di main. Kalau tidak, hentikan sekarang
# selagi belum ada yang berubah — bukan setelah setengah langkah terlanjur.

info "Memeriksa keadaan repo"

[ -d .git ] || gagal "Ini bukan repo git. Jalankan dari direktori aplikasi."

cabang_kini="$(git rev-parse --abbrev-ref HEAD)"
[ "$cabang_kini" = "$CABANG" ] || gagal "Sedang di cabang '$cabang_kini', seharusnya '$CABANG'.
Yang di-deploy selalu $CABANG. Pindah dulu: git checkout $CABANG"

if [ -n "$(git status --porcelain)" ]; then
    git status --short
    gagal "Ada perubahan yang belum di-commit di server.
Berkas di server tidak boleh diedit langsung. Periksa daftar di atas dulu."
fi

sukses "di cabang $CABANG, tidak ada perubahan lokal"

# --- Menarik perubahan ---------------------------------------------------

info "Menarik perubahan dari origin/$CABANG"

sebelum="$(git rev-parse HEAD)"
git fetch origin "$CABANG"

if [ "$sebelum" = "$(git rev-parse "origin/$CABANG")" ]; then
    sukses "sudah versi terbaru, tidak ada yang perlu ditarik"
else
    git log --oneline "HEAD..origin/$CABANG"
    # --ff-only: server tidak boleh membuat commit merge. Kalau tidak bisa
    # fast-forward, ada yang tidak beres dan harus dilihat manusia.
    git merge --ff-only "origin/$CABANG"
    sukses "diperbarui ke $(git rev-parse --short HEAD)"
fi

sesudah="$(git rev-parse HEAD)"

# Apakah berkas tertentu ikut berubah pada pull barusan?
berubah() {
    [ "$sebelum" = "$sesudah" ] && return 1
    ! git diff --quiet "$sebelum" "$sesudah" -- "$1"
}

# --- Dependensi ----------------------------------------------------------
# Dipasang ulang hanya kalau lock-nya memang berubah; memasang ulang setiap
# deploy cuma memperlambat tanpa mengubah apa pun.

if berubah composer.lock; then
    info "composer.lock berubah, memasang dependensi PHP"
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    sukses "dependensi PHP terpasang"
fi

if berubah package-lock.json; then
    info "package-lock.json berubah, memasang dependensi Node"
    # npm ci, bukan npm install: ia mengikuti lock apa adanya dan tidak
    # menulis ulang berkasnya, sehingga tidak memunculkan perubahan tak
    # terduga di server pada pull berikutnya.
    npm ci
    sukses "dependensi Node terpasang"
fi

# --- Migrasi -------------------------------------------------------------

info "Memeriksa migrasi"

tertunda="$(php artisan migrate:status 2>/dev/null | grep -ci 'pending' || true)"

if [ "$tertunda" -gt 0 ]; then
    php artisan migrate:status | grep -i 'pending' || true
    printf '\n'
    php artisan migrate --force
    sukses "$tertunda migrasi dijalankan"
else
    sukses "tidak ada migrasi tertunda"
fi

# --- Aset ----------------------------------------------------------------
# Selalu di-build. public/build tidak ikut git, jadi tidak ada cara memeriksa
# apakah ia sudah sesuai kode terbaru — dan gagalnya senyap: halaman tampil
# berantakan tanpa galat apa pun. Buildnya sendiri hanya beberapa detik.

info "Membangun aset"
npm run build
sukses "aset dibangun"

# --- Cache ---------------------------------------------------------------

info "Membersihkan cache"
php artisan optimize:clear
sukses "cache dibersihkan"

printf '\n\033[1;32mSelesai.\033[0m %s\n\n' "$(git log --oneline -1)"
