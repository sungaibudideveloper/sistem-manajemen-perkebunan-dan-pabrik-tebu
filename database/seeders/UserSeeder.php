<?php

namespace Database\Seeders;

use App\Models\Usercomp;
use App\Models\Username;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Username::create([
            // 'id' => 1,
            'usernm' => 'Admin',
            'name' => 'Admin',
            'password' => bcrypt('12345'),
            'permissions' => json_encode(["Master", "Company", "Create Company", "Edit Company", "Blok", "Create Blok", "Hapus Blok", "Edit Blok", "Plotting", "Create Plotting", "Hapus Plotting", "Edit Plotting", "Mapping", "Create Mapping", "Hapus Mapping", "Edit Mapping", "Kelola User", "Create User", "Hapus User", "Edit User", "Hak Akses", "Input Data", "Agronomi", "Create Agronomi", "Hapus Agronomi", "Edit Agronomi", "HPT", "Create HPT", "Hapus HPT", "Edit HPT", "Dashboard", "Dashboard Agronomi", "Pivot Agronomi", "Dashboard HPT", "Pivot HPT", "Report", "Report Agronomi", "Excel Agronomi", "Report HPT", "Excel HPT", "Process", "Process Posting", "Submit Posting", "Process Unposting", "Batal Posting", "Process Upload GPX File", "Process Export KML File", "Process Closing", "Create Notifikasi", "Hapus Notifikasi", "Edit Notifikasi", "Kepala Kebun", "Admin", "Menu Gudang", "Menu Pias"]),
            // 'id' => 1,
        ]);
        Usercomp::create([
            'usernm' => 'Admin',
            'companycode' => 'SB',
            'inputby' => 'Admin',
        ]);
    }
}
