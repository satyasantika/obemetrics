<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class obeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Permission::create(['name' => 'read bulk-import users'])->assignRole('admin');
        Permission::create(['name' => 'create bulk-import users'])->assignRole('admin');
        Permission::create(['name' => 'delete bulk-import users'])->assignRole('admin');

        Permission::create(['name' => 'create prodis'])->assignRole('admin');
        Permission::create(['name' => 'read prodis'])->syncRoles('admin','operator prodi','pimpinan prodi');
        Permission::create(['name' => 'update prodis'])->syncRoles('admin','operator prodi','pimpinan prodi');
        Permission::create(['name' => 'delete prodis'])->assignRole('admin');

        Permission::create(['name' => 'create join prodi users'])->assignRole('admin');
        Permission::create(['name' => 'read join prodi users'])->assignRole('admin');
        Permission::create(['name' => 'update join prodi users'])->assignRole('admin');
        Permission::create(['name' => 'delete join prodi users'])->assignRole('admin');

        Permission::create(['name' => 'read bulk-import joinprodiusers'])->assignRole('admin');
        Permission::create(['name' => 'create bulk-import joinprodiusers'])->assignRole('admin');
        Permission::create(['name' => 'delete bulk-import joinprodiusers'])->assignRole('admin');

        Permission::create(['name' => 'create semesters'])->assignRole('admin');
        Permission::create(['name' => 'read semesters'])->assignRole('admin');
        Permission::create(['name' => 'update semesters'])->assignRole('admin');
        Permission::create(['name' => 'delete semesters'])->assignRole('admin');

        Permission::create(['name' => 'create metodes'])->assignRole('admin');
        Permission::create(['name' => 'read metodes'])->assignRole('admin');
        Permission::create(['name' => 'update metodes'])->assignRole('admin');
        Permission::create(['name' => 'delete metodes'])->assignRole('admin');

        Permission::create(['name' => 'create evaluasis'])->assignRole('admin');
        Permission::create(['name' => 'read evaluasis'])->assignRole('admin');
        Permission::create(['name' => 'update evaluasis'])->assignRole('admin');
        Permission::create(['name' => 'delete evaluasis'])->assignRole('admin');

        Permission::create(['name' => 'create mahasiswas'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'read mahasiswas'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'update mahasiswas'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'delete mahasiswas'])->assignRole('admin','operator prodi');

        Permission::create(['name' => 'read bulk-import mahasiswas'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'create bulk-import mahasiswas'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'delete bulk-import mahasiswas'])->assignRole('admin','operator prodi');

        Permission::create(['name' => 'create kontrakmks'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'read kontrakmks'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'update kontrakmks'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'delete kontrakmks'])->assignRole('admin','operator prodi');

        Permission::create(['name' => 'read bulk-import kontrakmks'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'create bulk-import kontrakmks'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'delete bulk-import kontrakmks'])->assignRole('admin','operator prodi');

        Permission::create(['name' => 'create kurikulums'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read kurikulums'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update kurikulums'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete kurikulums'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create profils'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read profils'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update profils'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete profils'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create profil indikators'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read profil indikators'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update profil indikators'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete profil indikators'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create cpls'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read cpls'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update cpls'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete cpls'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create bks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read bks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update bks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete bks'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create mks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read mks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update mks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete mks'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'read bulk-import joinmkusers'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'create bulk-import joinmkusers'])->assignRole('admin','operator prodi');
        Permission::create(['name' => 'delete bulk-import joinmkusers'])->assignRole('admin','operator prodi');

        Permission::create(['name' => 'read join profil cpls'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update join profil cpls'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'read join cpl bks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update join cpl bks'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'read join bk mks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update join bk mks'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'read join mk users'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update join mk users'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create cpmks'])->syncRoles('koordinator mk','dosen');
        Permission::create(['name' => 'read cpmks'])->syncRoles('koordinator mk','dosen');
        Permission::create(['name' => 'update cpmks'])->syncRoles('koordinator mk','dosen');
        Permission::create(['name' => 'delete cpmks'])->syncRoles('koordinator mk','dosen');

        Permission::create(['name' => 'read join cpl cpmks'])->syncRoles('koordinator mk','dosen');
        Permission::create(['name' => 'update join cpl cpmks'])->syncRoles('koordinator mk','dosen');

        Permission::create(['name' => 'create subcpmks'])->assignRole('dosen');
        Permission::create(['name' => 'read subcpmks'])->assignRole('dosen');
        Permission::create(['name' => 'update subcpmks'])->assignRole('dosen');
        Permission::create(['name' => 'delete subcpmks'])->assignRole('dosen');

        Permission::create(['name' => 'create penugasans'])->assignRole('dosen');
        Permission::create(['name' => 'read penugasans'])->assignRole('dosen');
        Permission::create(['name' => 'update penugasans'])->assignRole('dosen');
        Permission::create(['name' => 'delete penugasans'])->assignRole('dosen');

        Permission::create(['name' => 'create nilais'])->assignRole('dosen');
        Permission::create(['name' => 'read nilais'])->assignRole('dosen');
        Permission::create(['name' => 'update nilais'])->assignRole('dosen');
        Permission::create(['name' => 'delete nilais'])->assignRole('dosen');

        Permission::create(['name' => 'read workcloud-mks'])->assignRole('dosen');
        Permission::create(['name' => 'read achievement-mks'])->assignRole('dosen');
        Permission::create(['name' => 'read ketercapaian-mks'])->assignRole('dosen');

        Permission::create(['name' => 'read join subcpmk penugasans'])->assignRole('dosen');
        Permission::create(['name' => 'update join subcpmk penugasans'])->assignRole('dosen');

    }
}
