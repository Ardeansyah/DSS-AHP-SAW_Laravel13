<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'atribut',
        'bobot',
    ];

    public function penilaians()
    {
        return $this->hasMany(Penilaian::class);
    }
}