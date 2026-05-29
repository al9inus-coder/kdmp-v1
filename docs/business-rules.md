# Business Rules KDMP

## Budget Master

Hierarki:

Program
→ Kegiatan
→ Sub Kegiatan
→ Rekening Belanja

Relasi:

1 Program memiliki banyak Kegiatan

1 Kegiatan memiliki banyak Sub Kegiatan

1 Sub Kegiatan memiliki banyak Rekening Belanja

---

## RUP

Setiap Paket RUP harus terkait dengan:

- Program
- Kegiatan
- Sub Kegiatan
- Rekening Belanja

---

## Pengadaan

Setiap paket pengadaan:

- berasal dari RUP
- memiliki nilai anggaran
- memiliki status

Status:

Draft
Persiapan
Pemilihan
Kontrak
Pelaksanaan
Selesai

---

## Swakelola

Jenis:

- Perjalanan Dinas
- Lembur
- Honorarium

Semua transaksi harus terkait dengan:

- Program
- Kegiatan
- Sub Kegiatan

---

## Monitoring

Monitoring terdiri dari:

- Progress Fisik (%)
- Progress Keuangan (%)
- Realisasi Anggaran

---

## Audit Trail

Semua perubahan data wajib menyimpan:

- user
- tanggal
- aktivitas