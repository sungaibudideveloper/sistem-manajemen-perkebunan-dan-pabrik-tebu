<?php

/**
 * Navigation Menu Configuration
 * 
 * BEST PRACTICE: Config-based menu (no database queries)
 * Used by: NavigationComposer, Sidebar component
 * 
 * Structure:
 * - Each menu item can have unlimited children (nested)
 * - 'permission' links to permission table (module.resource.action)
 * - 'route' is the Laravel route name
 * - 'icon' is the icon identifier (lucide icons)
 */

return [
    
    

    // ============================================
    // MASTER DATA
    // ============================================
    [
        'name' => 'Master Data',
        'icon' => 'database',
        'permission' => 'masterdata.menu.view',
        'children' => [
            [
                'name' => 'Company',
                'route' => 'masterdata.company.index',
                'permission' => 'masterdata.company.view',
            ],
            
            // Manajemen Lahan (Group)
            [
                'name' => 'Manajemen Lahan',
                'children' => [
                    [
                        'name' => 'Master List',
                        'route' => 'masterdata.master-list.index',
                        'permission' => 'masterdata.masterlist.view',
                    ],
                    [
                        'name' => 'Blok',
                        'route' => 'masterdata.blok.index',
                        'permission' => 'masterdata.blok.view',
                    ],
                    [
                        'name' => 'Batch',
                        'route' => 'masterdata.batch.index',
                        'permission' => 'masterdata.batch.view',
                    ],
                    [
                        'name' => 'Rekonstruksi Plot',
                        'route' => 'masterdata.split-merge-plot.index',
                        'permission' => 'masterdata.splitmergeplot.view',
                    ],
                ],
            ],
            
            // Data Agronomi (Group)
            [
                'name' => 'Data Agronomi',
                'children' => [
                    [
                        'name' => 'Kategori',
                        'route' => 'masterdata.kategori.index',
                        'permission' => 'masterdata.kategori.view',
                    ],
                    [
                        'name' => 'Herbisida',
                        'route' => 'masterdata.herbisida.index',
                        'permission' => 'masterdata.herbisida.view',
                    ],
                    [
                        'name' => 'Herbisida Group',
                        'route' => 'masterdata.herbisida-group.index',
                        'permission' => 'masterdata.herbisidagroup.view',
                    ],
                    [
                        'name' => 'Dosis Herbisida',
                        'route' => 'masterdata.herbisida-dosage.index',
                        'permission' => 'masterdata.herbisidadosage.view',
                    ],
                    [
                        'name' => 'Varietas',
                        'route' => 'masterdata.varietas.index',
                        'permission' => 'masterdata.varietas.view',
                    ],
                ],
            ],
            
            // Manajemen Personel & Aset (Group)
            [
                'name' => 'Manajemen Personel & Aset',
                'children' => [
                    [
                        'name' => 'Mandor',
                        'route' => 'masterdata.mandor.index',
                        'permission' => 'masterdata.mandor.view',
                    ],
                    [
                        'name' => 'Tenaga Kerja',
                        'route' => 'masterdata.tenagakerja.index',
                        'permission' => 'masterdata.tenagakerja.view',
                    ],
                    [
                        'name' => 'Kendaraan',
                        'route' => 'masterdata.kendaraan.index',
                        'permission' => 'masterdata.kendaraan.view',
                    ],
                    [
                        'name' => 'Kontraktor',
                        'route' => 'masterdata.kontraktor.index',
                        'permission' => 'masterdata.kontraktor.view',
                    ],
                    [
                        'name' => 'Subkontraktor',
                        'route' => 'masterdata.subkontraktor.index',
                        'permission' => 'masterdata.subkontraktor.view',
                    ],
                ],
            ],
            
            [
                'name' => 'Approval',
                'route' => 'masterdata.approval.index',
                'permission' => 'masterdata.approval.view',
            ],
            [
                'name' => 'Aktivitas',
                'route' => 'masterdata.aktivitas.index',
                'permission' => 'masterdata.aktivitas.view',
            ],
            [
                'name' => 'Upah',
                'route' => 'masterdata.upah.index',
                'permission' => 'masterdata.upah.view',
            ],
            [
                'name' => 'Accounting',
                'route' => 'masterdata.accounting.index',
                'permission' => 'masterdata.accounting.view',
            ],
        ],
    ],

    // ============================================
    // TRANSACTION (INPUT)
    // ============================================
    [
        'name' => 'Transaction',
        'icon' => 'file-edit',
        'permission' => 'input.menu.view',
        'children' => [
            [
                'name' => 'Rencana Kerja Harian',
                'route' => 'input.rencanakerjaharian.index',
                'permission' => 'input.rencanakerjaharian.view',
            ],
            [
                'name' => 'Rencana Kerja Mingguan',
                'route' => 'input.rencana-kerja-mingguan.index',
                'permission' => 'input.rencanakerjamingguan.view',
            ],
            [
                'name' => 'Agronomi',
                'route' => 'input.agronomi.index',
                'permission' => 'input.agronomi.view',
            ],
            [
                'name' => 'HPT',
                'route' => 'input.hpt.index',
                'permission' => 'input.hpt.view',
            ],
            [
                'name' => 'Gudang',
                'route' => 'input.gudang.index',
                'permission' => 'input.gudang.view',
            ],
            [
                'name' => 'Gudang BBM',
                'route' => 'input.gudang-bbm.index',
                'permission' => 'input.gudangbbm.view',
            ],
            [
                'name' => 'Kendaraan Workshop',
                'route' => 'input.kendaraan-workshop.index',
                'permission' => 'input.kendaraanworkshop.view',
            ],
            [
                'name' => 'Tebar Pias',
                'route' => 'input.pias.index',
                'permission' => 'input.pias.view',
            ],
            [
                'name' => 'NFC',
                'route' => 'input.nfc.index',
                'permission' => 'input.nfc.view',
            ],
            [
                'name' => 'Mapping BSM',
                'route' => 'input.mapping-bsm.index',
                'permission' => 'input.mappingbsm.view',
            ],
        ],
    ],

    // ============================================
    // REPORT
    // ============================================
    [
        'name' => 'Report',
        'icon' => 'file-text',
        'permission' => 'report.menu.view',
        'children' => [
            [
                'name' => 'Agronomi',
                'route' => 'report.agronomi.index',
                'permission' => 'report.agronomi.view',
            ],
            [
                'name' => 'HPT',
                'route' => 'report.hpt.index',
                'permission' => 'report.hpt.view',
            ],
            [
                'name' => 'ZPK',
                'route' => 'report.report-zpk.index',
                'permission' => 'report.zpk.view',
            ],
            [
                'name' => 'Manajemen Lahan',
                'route' => 'report.report-manajemen-lahan.index',
                'permission' => 'report.manajemenlahan.view',
            ],
            [
                'name' => 'Berita Acara Panen Tebu Giling',
                'route' => 'report.panen-tebu-report.index',
                'permission' => 'report.panentebu.view',
            ],
            [
                'name' => 'Surat Jalan',
                'route' => 'report.report-surat-jalan.index',
                'permission' => 'report.suratjalan.view',
            ],
            [
                'name' => 'Surat Jalan & Timbangan',
                'route' => 'report.report-surat-jalan-timbangan.index',
                'permission' => 'report.suratjalantimbangan.view',
            ],
            [
                'name' => 'Panen Track Plot',
                'route' => 'report.panen-track-plot.index',
                'permission' => 'report.panentrackplot.view',
            ],
            [
                'name' => 'Rekap Upah Mingguan',
                'route' => 'report.rekap-upah-mingguan.index',
                'permission' => 'report.rekapupahminggu.view',
            ],
            [
                'name' => 'Trash',
                'route' => 'report.trash-report.index',
                'permission' => 'report.trash.view',
            ],
        ],
    ],


    // ============================================
    // DASHBOARD
    // ============================================
    [
        'name' => 'Dashboard',
        'icon' => 'layout-dashboard',
        'permission' => 'dashboard.menu.view',
        'children' => [
            [
                'name' => 'Agronomi',
                'route' => 'dashboard.agronomi',
                'permission' => 'dashboard.agronomi.view',
            ],
            [
                'name' => 'HPT',
                'route' => 'dashboard.hpt',
                'permission' => 'dashboard.hpt.view',
            ],
            [
                'name' => 'Timeline',
                'route' => 'dashboard.timeline',
                'permission' => 'dashboard.timeline.view',
            ],
            [
                'name' => 'Timeline Plot',
                'route' => 'dashboard.timeline-plot',
                'permission' => 'dashboard.timelineplot.view',
            ],
            [
                'name' => 'Maps',
                'route' => 'dashboard.maps',
                'permission' => 'dashboard.maps.view',
            ],
        ],
    ],

    // ============================================
    // PROCESS
    // ============================================
    [
        'name' => 'Process',
        'icon' => 'settings',
        'permission' => 'process.menu.view',
        'children' => [
            [
                'name' => 'Posting',
                'route' => 'process.posting',
                'permission' => 'process.posting.view',
            ],
            [
                'name' => 'Unposting',
                'route' => 'process.unposting',
                'permission' => 'process.unposting.view',
            ],
            [
                'name' => 'Upload GPX File',
                'route' => 'upload.gpx.view',
                'permission' => 'process.uploadgpx.view',
            ],
            [
                'name' => 'Export KML File',
                'route' => 'export.kml.view',
                'permission' => 'process.exportkml.view',
            ],
            [
                'name' => 'Closing',
                'route' => 'process.closing',
                'permission' => 'process.closing.view',
            ],
        ],
    ],

    // ============================================
    // USER MANAGEMENT
    // ============================================
    [
        'name' => 'User Management',
        'icon' => 'users',
        'permission' => 'usermanagement.menu.view',
        'children' => [
            [
                'name' => 'User',
                'route' => 'usermanagement.user.index',
                'permission' => 'usermanagement.user.view',
            ],
            [
                'name' => 'User Company Access',
                'route' => 'usermanagement.user-company-permissions.index',
                'permission' => 'usermanagement.usercompany.view',
            ],
            [
                'name' => 'User Permissions',
                'route' => 'usermanagement.user-permissions.index',
                'permission' => 'usermanagement.userpermission.view',
            ],
            [
                'name' => 'User Activity Permission',
                'route' => 'usermanagement.user-activity-permission.index',
                'permission' => 'usermanagement.useractivity.view',
            ],
            [
                'name' => 'Master Permissions',
                'route' => 'usermanagement.permissions-masterdata.index',
                'permission' => 'usermanagement.permission.view',
            ],
            [
                'name' => 'Jabatan Management',
                'route' => 'usermanagement.jabatan.index',
                'permission' => 'usermanagement.jabatan.view',
            ],
            [
                'name' => 'Support Ticket',
                'route' => 'usermanagement.support-ticket.index',
                'permission' => 'usermanagement.supportticket.view',
            ],
        ],
    ],

    // ============================================
    // PABRIK
    // ============================================
    [
        'name' => 'Pabrik',
        'icon' => 'building',
        'permission' => 'pabrik.menu.view',
        'children' => [
            [
                'name' => 'Trash',
                'route' => 'pabrik.trash.index',
                'permission' => 'pabrik.trash.view',
            ],
            [
                'name' => 'Dashboard Panen Pabrik',
                'route' => 'pabrik.panen-pabrik.index',
                'permission' => 'pabrik.panenpabrik.view',
            ],
        ],
    ],
    
];