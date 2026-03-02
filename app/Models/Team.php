<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Agar kolom JSON otomatis diubah jadi Array saat diambil
    protected $casts = [
        'available_surveys' => 'array',
    ];

    // Relasi: Satu Tim punya banyak Penempatan
    public function placements()
    {
        return $this->hasMany(Placement::class);
    }
}
