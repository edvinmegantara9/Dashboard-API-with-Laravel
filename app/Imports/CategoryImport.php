<?php

namespace App\Imports;

use App\Models\Lp2b;
use App\Models\RekapIrigasi;
use App\Models\Ska;
use App\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;

class CategoryImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Lp2b([
            'name' => $row['name'],
        ]);
    }
}
