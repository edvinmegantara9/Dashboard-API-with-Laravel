<?php

namespace Database\Seeders;

use App\Models\Roles;
use App\Models\RolesOpds;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesOpdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Roles::where('is_opd', false)->first();
        for ($i=0; $i < 10; $i++) {
            if($i + 1 == $role->id) continue;
            $opd = Roles::find($i + 1);
            if(!$opd->is_opd) continue;
            RolesOpds::create([
                'role_id' => $role->id,
                'opd_id' => $i + 1
            ]);
        }
    }
}
