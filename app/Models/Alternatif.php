<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Alternatif extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'kelas',
        'ket_c3',
        'ket_c4',
    ];

    protected function nama(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => mb_strtoupper(trim((string) $value), 'UTF-8'),
            set: fn (?string $value) => mb_strtoupper(preg_replace('/\s+/', ' ', trim((string) $value)), 'UTF-8'),
        );
    }

    protected function kelas(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => mb_strtoupper(trim((string) $value), 'UTF-8'),
            set: fn (?string $value) => mb_strtoupper(preg_replace('/\s+/', ' ', trim((string) $value)), 'UTF-8'),
        );
    }

    public function penilaians()
    {
        return $this->hasMany(Penilaian::class);
    }
}