<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Mitra extends Model
{
    use HasFactory;

    protected $table = 'mitra';
    protected $primaryKey = 'id_mitra';
    public $timestamps = false;

    protected $fillable = [
        'id_kecamatan',
        'id_kabupaten',
        'id_provinsi',
        'id_desa',
        'sobat_id',
        'nama_lengkap',
        'alamat_mitra',
        'jenis_kelamin',
        'status_pekerjaan',
        'detail_pekerjaan',
        'no_hp_mitra',
        'email_mitra',
        'tahun',
        'tahun_selesai',
    ];

    // Relasi dengan Provinsi
    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'id_provinsi', 'id_provinsi');
    }

    // Relasi dengan Kabupaten
    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'id_kabupaten', 'id_kabupaten');
    }

    // Relasi dengan Kecamatan
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan', 'id_kecamatan');
    }

    // Relasi dengan Desa
    public function desa()
    {
        return $this->belongsTo(Desa::class, 'id_desa', 'id_desa');
    }

    // Relasi ke MitraSurvei (one-to-many)
    public function mitraSurveis()
    {
        return $this->hasMany(MitraSurvei::class, 'id_mitra', 'id_mitra');
    }

    // Relasi many-to-many dengan Survei melalui pivot table mitra_survei
    public function surveis()
    {
        return $this->belongsToMany(Survei::class, 'mitra_survei', 'id_mitra', 'id_survei')
            ->using(MitraSurvei::class)
            ->withPivot([
                'vol',
                'nilai',
                'catatan',
                'id_posisi_mitra',
                'tgl_ikut_survei'
            ])
            ->withTimestamps();
    }

    /**
     * Relasi ke tabel Placements.
     * Menghubungkan Mitra KANAL (id_mitra) ke tabel baru placements (mitra_id)
     */
    public function placements()
    {
        return $this->hasMany(Placement::class, 'mitra_id', 'id_mitra');
    }

    /**
     * Helper: Cek apakah mitra aktif di tahun tertentu (berdasarkan kontrak)
     */
    public function isActiveInYear($year)
    {
        if (!$this->tahun) return false;

        $startYear = Carbon::parse($this->tahun)->year;

        // Jika tahun_selesai null, anggap masih aktif sampai sekarang/selamanya
        $endYear = $this->tahun_selesai ? Carbon::parse($this->tahun_selesai)->year : $year + 1;

        return $year >= $startYear && $year <= $endYear;
    }

    /**
     * Helper: Cek status penempatan mitra di bulan & tahun tertentu.
     * Digunakan di Dashboard Penempatan untuk menampilkan kotak tim.
     */
    public function activeTeamsStatus($month, $year)
    {
        // Ambil data penempatan mitra ini di bulan & tahun yang diminta
        $placements = $this->placements()
            ->where('month', $month)
            ->where('year', $year)
            ->with('team')
            ->get();

        // Format data agar mudah diloop di Blade
        $details = $placements->groupBy('team_id')->map(function ($group) {
            $first = $group->first();
            $surveys = [];

            // Kumpulkan semua survei dalam satu tim (jika ada multiple entry, meski biasanya 1)
            foreach ($group as $p) {
                if ($p->survey_1) $surveys[] = $p->survey_1 . " (Vol: $p->vol_1)";
                if ($p->survey_2) $surveys[] = $p->survey_2 . " (Vol: $p->vol_2)";
                if ($p->survey_3) $surveys[] = $p->survey_3 . " (Vol: $p->vol_3)";
            }

            return [
                'id' => $first->id, // ID Placement untuk tombol hapus
                'team_id' => $first->team_id,
                'team_name' => $first->team->name ?? 'Unknown Team',
                'status_anggota' => $first->status_anggota,
                'surveys' => $surveys
            ];
        });

        // Hitung sisa slot (Logika sederhana: 1 Mitra = 1 Slot Tim Utama)
        // Di sini kita asumsikan mitra bisa masuk ke maksimal 2 tim berbeda (opsional)
        // Atau return true jika belum ada tim sama sekali

        $count = $placements->count();
        $remaining = 10; // Unlimited assignment, atau batasi jika perlu (misal: if count > 0 then 0)

        return [
            'team_details' => $details,
            'slot_count' => $count,
            'remaining_slots' => $remaining,
            'status_text' => $count > 0 ? 'Sudah Ditugaskan' : 'Belum Ada Tugas'
        ];
    }
}
