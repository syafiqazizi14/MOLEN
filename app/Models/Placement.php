<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    use HasFactory;

    protected $fillable = [
        'mitra_id',
        'team_id',
        'month',
        'year',
        'survey_1',
        'vol_1',
        'survey_2',
        'vol_2',
        'survey_3',
        'vol_3',
        'status_anggota'
    ];

    // Casting untuk memastikan tipe data yang tepat
    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'mitra_id' => 'integer',
        'team_id' => 'integer',
        'vol_1' => 'decimal:2',
        'vol_2' => 'decimal:2',
        'vol_3' => 'decimal:2',
    ];

    // Relasi ke Mitra (PENTING: Sesuaikan dengan struktur tabel Mitra Anda)
    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id', 'id_mitra'); // Sesuaikan 'id' jika PK mitra beda
    }

    // Relasi ke Tim
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
}
