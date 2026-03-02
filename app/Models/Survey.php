<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    // Asumsi tabel survei memiliki nama: surveys, dan kolom: id, name
    protected $fillable = ['name', 'description']; // Tambahkan kolom yang relevan

    // Relasi: Satu Survei dimiliki oleh banyak Penempatan
    public function placements()
    {
        return $this->hasMany(Placement::class);
    }
}
