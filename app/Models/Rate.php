<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    // Izinkan semua kolom diisi
    protected $guarded = [];

    // Casting untuk memastikan tipe data yang tepat
    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'cost' => 'decimal:2',
    ];

    // Relasi ke Team (agar di tabel bisa muncul nama timnya)
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
