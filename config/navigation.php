<?php

return [

    // ─── DASHBOARD (Admin / Kabid) ──────────────────────────────
    [
        'title' => 'Dashboard',
        'route' => 'dashboard.admin',
        'icon'  => 'layout-dashboard',
        'roles' => ['Admin'],
    ],
    [
        'title' => 'Dashboard',
        'route' => 'dashboard.kabid',
        'icon'  => 'layout-dashboard',
        'roles' => ['Kabid'],
    ],
    [
        'title' => 'Kalender',
        'route' => 'kabid.kalender.index',
        'icon'  => 'calendar-days',
        'roles' => ['Kabid'],
    ],

    // ─── STAF MENU ───────────────────────────────────────────────────────────
    [
        'title' => 'Dashboard',
        'route' => 'dashboard.staf',
        'icon'  => 'layout-dashboard',
        'roles' => ['Staff'],
    ],

    [
        'title'  => 'PENGADAAN',
        'type'   => 'group',
        'roles'  => ['Staff'],
        'children' => [
            [
                'title' => 'Paket RUP',
                'route' => 'staf.packages.index',
                'icon'  => 'package',
                'roles' => ['Staff'],
            ],
            [
                'title' => 'Tambah Paket',
                'route' => 'staf.packages.create',
                'icon'  => 'plus-circle',
                'roles' => ['Staff'],
            ],
            [
                'title' => 'Impor Paket',
                'route' => 'staf.packages.import',
                'icon'  => 'upload',
                'roles' => ['Staff'],
            ],
        ],
    ],
    [
        'title'  => 'PERJALANAN DINAS',
        'type'   => 'group',
        'roles'  => ['Staff'],
        'children' => [
            [
                'title' => 'Kalender',
                'route' => 'staf.kalender.index',
                'icon'  => 'calendar-days',
                'roles' => ['Staff'],
            ],
            [
                'title' => 'Daftar SPD',
                'route' => 'staf.sppd.index',
                'icon'  => 'plane',
                'roles' => ['Staff'],
            ],
            [
                'title' => 'Ajukan SPD',
                'route' => 'staf.sppd.create',
                'icon'  => 'plus-circle',
                'roles' => ['Staff'],
            ],
        ],
    ],
    [
        'title'  => 'LEMBUR',
        'type'   => 'group',
        'roles'  => ['Staff'],
        'children' => [
            [
                'title' => 'Input Lembur',
                'route' => 'staf.lembur.index',
                'icon'  => 'calendar-clock',
                'roles' => ['Staff'],
            ],
        ],
    ],
    [
        'title'  => 'LAPORAN',
        'type'   => 'group',
        'roles'  => ['Staff'],
        'children' => [
            [
                'title' => 'Arsip Dokumen',
                'route' => 'staf.arsip.index',
                'icon'  => 'folder-open',
                'roles' => ['Staff'],
            ],
        ],
    ],

    // ─── KABID MENU ──────────────────────────────────────────────────────────
    [
        'title'  => 'PERENCANAAN',
        'type'   => 'group',
        'roles'  => ['Kabid'],
        'children' => [
            [
                'title' => 'Paket RUP',
                'route' => 'kabid.packages.index',
                'icon'  => 'file-search',
                'badge' => 'kabid_paket_pending',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Daftar SPD',
                'route' => 'kabid.sppd.index',
                'icon'  => 'plane',
                'badge' => 'kabid_sppd_pending',
                'roles' => ['Kabid'],
            ],
        ],
    ],

    // ─── ADMIN MENU ──────────────────────────────────────────────────────────
    [
        'title'  => 'PENGADAAN',
        'type'   => 'group',
        'roles'  => ['Admin'],
        'children' => [
            [
                'title' => 'Paket RUP',
                'route' => 'admin.packages.index',
                'icon'  => 'briefcase',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'Paket Pengadaan',
                'icon'  => 'package',
                'roles' => ['Admin'],
                'children' => [
                    [
                        'title' => 'Penyedia',
                        'route' => 'admin.penyedia.index',
                        'icon'  => 'briefcase-business',
                        'roles' => ['Admin'],
                    ],
                    [
                        'title' => 'Swakelola',
                        'route' => 'admin.swakelola.index',
                        'icon'  => 'handshake',
                        'roles' => ['Admin'],
                    ],
                    [
                        'title' => 'Dikecualikan',
                        'route' => 'admin.dikecualikan.index',
                        'icon'  => 'file-warning',
                        'roles' => ['Admin'],
                    ],
                ]
            ],
            [
                'title' => 'Jadwal Pengadaan',
                'route' => 'admin.schedules.index',
                'icon'  => 'calendar-clock',
                'roles' => ['Admin'],
            ],
        ],
    ],

    // ─── KABID PENGADAAN MENU ────────────────────────────────────────────────
    // Tahapan (persiapan/pemilihan/pelaksanaan/selesai) diakses lewat kartu
    // pipeline di halaman Penyedia — bukan lewat sidebar.
    [
        'title'  => 'PENGADAAN',
        'type'   => 'group',
        'roles'  => ['Kabid'],
        'children' => [
            [
                'title' => 'Penyedia',
                'route' => 'kabid.penyedia.index',
                'icon'  => 'briefcase-business',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Swakelola',
                'route' => 'kabid.swakelola.index',
                'icon'  => 'handshake',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Dikecualikan',
                'route' => 'kabid.dikecualikan.index',
                'icon'  => 'file-warning',
                'roles' => ['Kabid'],
            ],
        ],
    ],

    // ─── MASTER DATA (Admin only) ─────────────────────────────────────────────
    [
        'title'  => 'MASTER',
        'type'   => 'group',
        'roles'  => ['Admin'],
        'children' => [
            [
                'title' => 'Program',
                'route' => 'admin.programs.index',
                'icon'  => 'folder',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'Kegiatan',
                'route' => 'admin.activities.index',
                'icon'  => 'briefcase',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'Sub Kegiatan',
                'route' => 'admin.sub-activities.index',
                'icon'  => 'layers',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'Rekening',
                'route' => 'admin.accounts.index',
                'icon'  => 'hash',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'SKPD',
                'route' => 'admin.skpds.index',
                'icon'  => 'building-2',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'Pegawai',
                'route' => 'admin.employees.index',
                'icon'  => 'id-card',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'Data SBU',
                'icon'  => 'database',
                'roles' => ['Admin'],
                'children' => [
                    [
                        'title' => 'SBU Uang Harian',
                        'route' => 'admin.sbu-uang-harians.index',
                        'icon'  => 'banknote',
                        'roles' => ['Admin'],
                    ],
                    [
                        'title' => 'SBU Penginapan',
                        'route' => 'admin.sbu-penginapans.index',
                        'icon'  => 'bed',
                        'roles' => ['Admin'],
                    ],
                    [
                        'title' => 'SBU Tiket Pesawat',
                        'route' => 'admin.sbu-tiket-pesawats.index',
                        'icon'  => 'plane',
                        'roles' => ['Admin'],
                    ],
                    [
                        'title' => 'SBU Transportasi',
                        'route' => 'admin.sbu-transport-rates.index',
                        'icon'  => 'car',
                        'roles' => ['Admin'],
                    ],
                    [
                        'title' => 'SBU Lembur',
                        'route' => 'admin.sbu-lemburs.index',
                        'icon'  => 'clock',
                        'roles' => ['Admin'],
                    ],
                ],
            ],
        ],
    ],

    // ─── LAPORAN ─────────────────────────────────────────────────────────────
    [
        'title'  => 'LAPORAN',
        'type'   => 'group',
        'roles'  => ['Admin'],
        'children' => [
            [
                'title' => 'Monitoring',
                'route' => 'admin.monev.index',
                'icon'  => 'pie-chart',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'Buku Register',
                'route' => 'admin.buku-register.index',
                'icon'  => 'book-marked',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'Arsip Dokumen',
                'route' => 'admin.arsip.index',
                'icon'  => 'folder-open',
                'roles' => ['Admin'],
            ],
        ],
    ],
    [
        'title'  => 'PELAPORAN',
        'type'   => 'group',
        'roles'  => ['Kabid'],
        'children' => [
            [
                'title' => 'Monitoring',
                'route' => 'kabid.monev.index',
                'icon'  => 'pie-chart',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Buku Register',
                'route' => 'kabid.buku-register.index',
                'icon'  => 'book-marked',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Arsip Dokumen',
                'route' => 'kabid.arsip.index',
                'icon'  => 'folder-open',
                'roles' => ['Kabid'],
            ],
        ],
    ],

    // ─── ADMINISTRASI (Admin only) ────────────────────────────────────────────
    [
        'title'  => 'ADMINISTRASI',
        'type'   => 'group',
        'roles'  => ['Admin'],
        'children' => [
            [
                'title' => 'Koreksi Data',
                'route' => 'admin.data-corrections.index',
                'icon'  => 'file-pen-line',
                'roles' => ['Admin'],
            ],
        ],
    ],

    // ─── PENGATURAN (Admin only) ──────────────────────────────────────────────
    [
        'title'  => 'PENGATURAN',
        'type'   => 'group',
        'roles'  => ['Admin'],
        'children' => [
            [
                'title' => 'Tahun Anggaran',
                'route' => 'admin.fiscal-years.index',
                'icon'  => 'calendar',
                'roles' => ['Admin'],
            ],
            [
                'title' => 'Manajemen User',
                'route' => 'admin.users.index',
                'icon'  => 'users',
                'roles' => ['Admin'],
            ],
        ],
    ],
];
