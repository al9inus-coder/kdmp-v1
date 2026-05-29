# Database Design

## users

- id
- name
- email
- password

---

## programs

- id
- kode_program
- nama_program
- tahun
- status

---

## activities

- id
- program_id
- kode_kegiatan
- nama_kegiatan

---

## sub_activities

- id
- activity_id
- kode_sub_kegiatan
- nama_sub_kegiatan

---

## accounts

- id
- sub_activity_id
- kode_rekening
- nama_rekening

---

## rup_packages

- id
- account_id
- nama_paket
- pagu
- metode

---

## procurements

- id
- rup_package_id
- status
- nilai_kontrak

---

## travels

- id
- sub_activity_id
- tujuan
- tanggal_berangkat
- tanggal_kembali

---

## overtimes

- id
- sub_activity_id
- tanggal
- jumlah_jam

---

## activity_logs

- id
- user_id
- activity
- created_at