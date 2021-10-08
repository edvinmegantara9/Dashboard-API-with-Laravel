<?php

namespace Database\Seeders;

use App\Models\Roles;
use Illuminate\Database\Seeder;

use function PHPUnit\Framework\isEmpty;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $opd = [
            'DINAS A',
            'DINAS B',
            'DINAS C',
            'DINAS D',
            'DINAS E',
            'DINAS F',
            'DINAS G',
            'DINAS H',
            'DINAS I',
            'DINAS J',
            'DINAS K',
            'DINAS L',
            'DINAS M',
            'DINAS N',
        ];

        $nonOpd = [
            'BAPPEDA 1',
            'BAPPEDA 2',
            'BAPPEDA 3'
        ];

        while(!empty($nonOpd))
        {
            shuffle($nonOpd);
            Roles::create([
                'name' => array_pop($nonOpd),
                'is_opd' => 0
            ]);
        }

        while(!empty($opd))
        {
            Roles::create([
                'name' => array_pop($opd),
                'is_opd' => 1
            ]);
        }
        // Roles::factory()->count(10)->create();
    }
}
