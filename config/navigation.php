<?php

return [

    // ─── DASHBOARD (Admin / Super Admin / Kabid) ──────────────────────────────
    [
        'title' => 'Dashboard',
        'route' => 'dashboard.admin',
        'icon'  => 'layout-dashboard',
        'roles' => ['Admin', 'Super Admin'],
    ],
    [
        'title' => 'Dashboard',
        'route' => 'dashboard.kabid',
        'icon'  => 'layout-dashboard',
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
                'title' => 'SPPD',
                'route' => 'staf.sppd.index',
                'icon'  => 'plane',
                'roles' => ['Staff'],
            ],
        ],
    ],

    // ─── KABID MENU ──────────────────────────────────────────────────────────
    [
        'title'  => 'PERSETUJUAN',
        'type'   => 'group',
        'roles'  => ['Kabid'],
        'children' => [
            [
                'title' => 'Paket RUP',
                'route' => 'kabid.packages.index',
                'icon'  => 'file-search',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Pengajuan SPPD',
                'route' => 'kabid.sppd.index',
                'icon'  => 'plane',
                'roles' => ['Kabid'],
            ],
        ],
    ],

    // ─── ADMIN MENU ──────────────────────────────────────────────────────────
    [
        'title'  => 'PENGADAAN',
        'type'   => 'group',
        'roles'  => ['Admin', 'Super Admin'],
        'children' => [
            [
                'title' => 'Paket RUP',
                'route' => 'packages.index',
                'icon'  => 'briefcase',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Paket Pengadaan',
                'route' => 'procurement-packages.index',
                'icon'  => 'package',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Persiapan Pengadaan',
                'route' => 'procurement-requests.index',
                'icon'  => 'clipboard-list',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Proses Pengadaan',
                'route' => 'procurement-processes.index',
                'icon'  => 'refresh-cw',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Pelaksanaan',
                'route' => 'activities.index',
                'icon'  => 'play-circle',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Pembayaran',
                'route' => 'procurement-payments.index',
                'icon'  => 'credit-card',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Jadwal Pengadaan',
                'route' => 'schedules.index',
                'icon'  => 'calendar-clock',
                'roles' => ['Admin', 'Super Admin'],
            ],
        ],
    ],

    // ─── KABID PENGADAAN MENU ────────────────────────────────────────────────
    [
        'title'  => 'PENGADAAN',
        'type'   => 'group',
        'roles'  => ['Kabid'],
        'children' => [
            [
                'title' => 'Semua Paket',
                'route' => 'kabid.procurement-packages.index',
                'exact_query' => true,
                'icon'  => 'package',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Penyedia',
                'route' => 'kabid.procurement-packages.index',
                'params' => ['type' => 'penyedia'],
                'icon'  => 'briefcase-business',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Swakelola',
                'route' => 'kabid.procurement-packages.index',
                'params' => ['type' => 'swakelola'],
                'icon'  => 'handshake',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Dikecualikan',
                'route' => 'kabid.procurement-packages.index',
                'params' => ['type' => 'dikecualikan'],
                'icon'  => 'file-warning',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Persiapan',
                'route' => 'kabid.procurement-packages.index',
                'params' => ['status' => 'draft'],
                'icon'  => 'clipboard-list',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Pemilihan',
                'route' => 'kabid.procurement-packages.index',
                'params' => ['status' => 'persiapan'],
                'icon'  => 'refresh-cw',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Pelaksanaan',
                'route' => 'kabid.procurement-packages.index',
                'params' => ['status' => 'diproses'],
                'icon'  => 'play-circle',
                'roles' => ['Kabid'],
            ],
            [
                'title' => 'Selesai',
                'route' => 'kabid.procurement-packages.index',
                'params' => ['status' => 'selesai'],
                'icon'  => 'check-circle',
                'roles' => ['Kabid'],
            ],
        ],
    ],

    // ─── MASTER DATA (Admin only) ─────────────────────────────────────────────
    [
        'title'  => 'MASTER',
        'type'   => 'group',
        'roles'  => ['Admin', 'Super Admin'],
        'children' => [
            [
                'title' => 'Program',
                'route' => 'programs.index',
                'icon'  => 'folder',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Kegiatan',
                'route' => 'activities.index',
                'icon'  => 'briefcase',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Sub Kegiatan',
                'route' => 'sub-activities.index',
                'icon'  => 'layers',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Rekening',
                'route' => 'accounts.index',
                'icon'  => 'hash',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'SKPD',
                'route' => 'skpds.index',
                'icon'  => 'building-2',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Pegawai',
                'route' => 'employees.index',
                'icon'  => 'id-card',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Data SBU',
                'icon'  => 'database',
                'roles' => ['Admin', 'Super Admin'],
                'children' => [
                    [
                        'title' => 'SBU Uang Harian',
                        'route' => 'sbu-uang-harians.index',
                        'icon'  => 'banknote',
                        'roles' => ['Admin', 'Super Admin'],
                    ],
                    [
                        'title' => 'SBU Penginapan',
                        'route' => 'sbu-penginapans.index',
                        'icon'  => 'bed',
                        'roles' => ['Admin', 'Super Admin'],
                    ],
                    [
                        'title' => 'SBU Tiket Pesawat',
                        'route' => 'sbu-tiket-pesawats.index',
                        'icon'  => 'plane',
                        'roles' => ['Admin', 'Super Admin'],
                    ],
                    [
                        'title' => 'SBU Transportasi',
                        'route' => 'sbu-transport-rates.index',
                        'icon'  => 'car',
                        'roles' => ['Admin', 'Super Admin'],
                    ],
                    [
                        'title' => 'SBU Lembur',
                        'route' => 'sbu-lemburs.index',
                        'icon'  => 'clock',
                        'roles' => ['Admin', 'Super Admin'],
                    ],
                ],
            ],
        ],
    ],

    // ─── LAPORAN ─────────────────────────────────────────────────────────────
    [
        'title'  => 'LAPORAN',
        'type'   => 'group',
        'roles'  => ['Admin', 'Super Admin'],
        'children' => [
            [
                'title' => 'Monitoring',
                'route' => 'monev.index',
                'icon'  => 'pie-chart',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Buku Register',
                'route' => 'admin.buku-register.index',
                'icon'  => 'book-marked',
                'roles' => ['Admin', 'Super Admin'],
            ],
        ],
    ],
    [
        'title'  => 'LAPORAN',
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
        ],
    ],

    // ─── PENGATURAN (Admin only) ──────────────────────────────────────────────
    [
        'title'  => 'PENGATURAN',
        'type'   => 'group',
        'roles'  => ['Admin', 'Super Admin'],
        'children' => [
            [
                'title' => 'Tahun Anggaran',
                'route' => 'fiscal-years.index',
                'icon'  => 'calendar',
                'roles' => ['Admin', 'Super Admin'],
            ],
            [
                'title' => 'Manajemen User',
                'route' => 'admin.users.index',
                'icon'  => 'users',
                'roles' => ['Admin', 'Super Admin'],
            ],
        ],
    ],
];
