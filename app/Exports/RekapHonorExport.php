<?php

namespace App\Exports;

use App\Models\Placement;
use App\Models\Rate;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// Pastikan use ini ada
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// [PERUBAHAN 1] Tambahkan ', WithColumnFormatting' di baris ini
class RekapHonorExport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    protected $bulan;
    protected $tahun;
    protected $team_id;
    protected $search;

    public function __construct($bulan, $tahun, $team_id, $search = null)
    {
        // Ensure tahun is always integer
        $this->tahun = (int) $tahun;
        
        // Cast bulan to integer if provided
        $this->bulan = !empty($bulan) ? (int) $bulan : null;
        
        $this->team_id = $team_id;
        $this->search = $search;
    }

    public function view(): View
    {
        // ... (Kode query dan logika penyusunan data SAMA PERSIS seperti sebelumnya) ...
        // ... Tidak ada perubahan di bagian query ini ...

        $query = Placement::with(['mitra', 'team'])
            ->whereRaw('CAST(year AS UNSIGNED) = ?', [$this->tahun]);

        if ($this->bulan) {
            $query->where('month', $this->bulan);
        }

        if ($this->team_id != 'all' && $this->team_id != null) {
            $query->where('team_id', $this->team_id);
        }

        if ($this->search) {
            $search = $this->search;
            $query->whereHas('mitra', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%");
            });
        }

        $placements = $query->orderBy('mitra_id')->get();
        $rates = Rate::whereRaw('CAST(year AS UNSIGNED) = ?', [$this->tahun])->get();
        $rateMap = [];
        foreach ($rates as $r) {
            $rateMap[$r->survey_name][$r->month] = $r->cost;
        }

        // Ambil data survei untuk KRO dan Jadwal dengan mapping fleksibel
        $surveiData = \App\Models\Survei::all();
        $surveyDetailMap = [];
        $surveyDetailMapNormalized = [];
        
        foreach ($surveiData as $s) {
            $detail = [
                'kro' => $s->kro ?? '-',
                'jadwal_kegiatan' => $s->jadwal_kegiatan ? date('d/m/Y', strtotime($s->jadwal_kegiatan)) : '-',
                'jadwal_berakhir' => $s->jadwal_berakhir_kegiatan ? date('d/m/Y', strtotime($s->jadwal_berakhir_kegiatan)) : '-',
            ];
            
            // Mapping exact (original)
            $surveyDetailMap[$s->nama_survei] = $detail;
            
            // Mapping normalized (lowercase, trim) untuk fallback
            $normalizedName = strtolower(trim($s->nama_survei));
            $surveyDetailMapNormalized[$normalizedName] = $detail;
        }
        
        // Helper function untuk mencari detail survei dengan fleksibel
        $getSurveyDetail = function($surveyName) use ($surveyDetailMap, $surveyDetailMapNormalized, $surveiData) {
            // 1. Coba exact match
            if (isset($surveyDetailMap[$surveyName])) {
                return $surveyDetailMap[$surveyName];
            }
            
            // 2. Coba normalized match (case-insensitive)
            $normalized = strtolower(trim($surveyName));
            if (isset($surveyDetailMapNormalized[$normalized])) {
                return $surveyDetailMapNormalized[$normalized];
            }
            
            // 3. Coba partial match (LIKE)
            foreach ($surveiData as $s) {
                if (stripos($s->nama_survei, $surveyName) !== false || stripos($surveyName, $s->nama_survei) !== false) {
                    return [
                        'kro' => $s->kro ?? '-',
                        'jadwal_kegiatan' => $s->jadwal_kegiatan ? date('d/m/Y', strtotime($s->jadwal_kegiatan)) : '-',
                        'jadwal_berakhir' => $s->jadwal_berakhir_kegiatan ? date('d/m/Y', strtotime($s->jadwal_berakhir_kegiatan)) : '-',
                    ];
                }
            }
            
            // 4. Default jika tidak ditemukan
            return [
                'kro' => '-',
                'jadwal_kegiatan' => '-',
                'jadwal_berakhir' => '-',
            ];
        };

        $uniqueSurveys = [];
        $surveyTeamMap = [];
        $surveyDetailMapFinal = []; // Map final untuk dikirim ke view
        $rekapMitra = [];

        foreach ($placements as $p) {
            $teamName = $p->team->name ?? '-';
            if ($p->survey_1) {
                $uniqueSurveys[$p->survey_1] = $p->survey_1;
                $surveyTeamMap[$p->survey_1] = $teamName;
                // Resolve detail survei dengan fungsi fleksibel
                if (!isset($surveyDetailMapFinal[$p->survey_1])) {
                    $surveyDetailMapFinal[$p->survey_1] = $getSurveyDetail($p->survey_1);
                }
            }
            if ($p->survey_2) {
                $uniqueSurveys[$p->survey_2] = $p->survey_2;
                $surveyTeamMap[$p->survey_2] = $teamName;
                if (!isset($surveyDetailMapFinal[$p->survey_2])) {
                    $surveyDetailMapFinal[$p->survey_2] = $getSurveyDetail($p->survey_2);
                }
            }
            if ($p->survey_3) {
                $uniqueSurveys[$p->survey_3] = $p->survey_3;
                $surveyTeamMap[$p->survey_3] = $teamName;
                if (!isset($surveyDetailMapFinal[$p->survey_3])) {
                    $surveyDetailMapFinal[$p->survey_3] = $getSurveyDetail($p->survey_3);
                }
            }

            $mid = $p->mitra_id;
            if (!isset($rekapMitra[$mid])) {
                $rekapMitra[$mid] = [
                    'nama' => $p->mitra->nama_lengkap ?? '-',
                    'sobat_id' => $p->mitra->sobat_id ?? '-',
                    'kode_kec' => $p->mitra->kode_kec ?? $p->mitra->id_kecamatan ?? '-',
                    'surveys_data' => [],
                    'grand_total' => 0
                ];
            }

            $processSurvey = function ($name, $vol) use (&$rekapMitra, $mid, $rateMap, $p) {
                if (!$name || $vol <= 0) return;
                $harga = $rateMap[$name][$p->month] ?? 0;
                $total = $vol * $harga;
                $rekapMitra[$mid]['grand_total'] += $total;
                if (isset($rekapMitra[$mid]['surveys_data'][$name])) {
                    $rekapMitra[$mid]['surveys_data'][$name]['vol'] += $vol;
                    $rekapMitra[$mid]['surveys_data'][$name]['total'] += $total;
                } else {
                    $rekapMitra[$mid]['surveys_data'][$name] = [
                        'vol' => $vol,
                        'honor_satuan' => $harga,
                        'total' => $total
                    ];
                }
            };
            $processSurvey($p->survey_1, $p->vol_1);
            $processSurvey($p->survey_2, $p->vol_2);
            $processSurvey($p->survey_3, $p->vol_3);
        }

        sort($uniqueSurveys);

        return view('exports.excel_bulanan', [
            'rekapMitra' => $rekapMitra,
            'uniqueSurveys' => $uniqueSurveys,
            'surveyTeamMap' => $surveyTeamMap,
            'surveyDetailMap' => $surveyDetailMapFinal,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun
        ]);
    }

    // [PERUBAHAN 2] Fungsi ini WAJIB ada untuk mengatasi masalah E+11
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // Paksa Kolom C (SOBAT ID) jadi Text
        ];
    }
}
