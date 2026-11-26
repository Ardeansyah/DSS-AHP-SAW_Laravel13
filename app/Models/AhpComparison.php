<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AhpComparison extends Model
{
    protected $fillable = [
        'kriteria_1_id',
        'kriteria_2_id',
        'nilai',
    ];
}