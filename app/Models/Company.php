<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    public function company_services()
    {
        return $this->hasMany(CompanyService::class, 'company_id', 'id');
    }

    public function company_clients()
    {
        return $this->hasMany(CompanyClient::class, 'company_id', 'id');
    }
}
