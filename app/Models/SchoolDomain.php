<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolDomain extends Model
{
    protected $table = 'school_domains';  // explicitly set table name

    protected $fillable = [
        'school_name',
        'school_code',
        'school_phone',
        'country_region',
        'timezone',
        'school_domain',
    ];

}

