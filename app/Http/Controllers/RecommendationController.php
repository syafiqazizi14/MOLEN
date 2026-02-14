<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mitra;
use App\Models\Placement;
use App\Models\Rate;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n')); // <--- Filter Bulan (Default Bulan Ini)

        // 1. Ambil Data Referensi
        $teams = Team::all();
        $rates = Rate::pluck('cost', 'survey_name')->toArray();

        // 2. Ambil Mitra & Placement KHUSUS Bulan & Tahun Terpilih
       $mitras = Mitra::where('jenis_mitra', 'Rutin')
                ->with(['placements' => function ($q) use ($year, $month) {
                    $q->where('year', $year)
                      ->where('month', $month);
                }])
                ->get();


        // 3. LOGIKA HITUNG & SORTING
        $rankedMitras = $mitras->map(function ($mitra) use ($rates) {
            $totalHonor = 0;
            $frekuensiKerja = 0;

            foreach ($mitra->placements as $p) {
                // Hitung honor
                $totalHonor += ($p->vol_1 * ($rates[$p->survey_1] ?? 0));
                if ($p->survey_2) $totalHonor += ($p->vol_2 * ($rates[$p->survey_2] ?? 0));
                if ($p->survey_3) $totalHonor += ($p->vol_3 * ($rates[$p->survey_3] ?? 0));

                $frekuensiKerja++;
            }

            $mitra->monthly_income = $totalHonor; // Ubah nama jadi monthly_income biar jelas
            $mitra->job_count = $frekuensiKerja;

            return $mitra;
        })
            ->sortBy('monthly_income') // Urutkan dari yang termiskin di bulan ini
            ->values();

        // Filter Pencarian Nama (Tetap sama)
        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);
            $rankedMitras = $rankedMitras->filter(function ($m) use ($search) {
                return str_contains(strtolower($m->nama_lengkap), $search) ||
                    str_contains($m->sobat_id, $search);
            });
        }
        
        // ✅ JUMLAH HASIL SETELAH FILTER
            $totalFiltered = $rankedMitras->count();

        // Pagination (Tetap sama)
            $perPage = 20;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            
            $currentItems = $rankedMitras
                ->slice(($currentPage - 1) * $perPage, $perPage)
                ->values();
            
            $paginatedMitras = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentItems,
                $rankedMitras->count(),
                $perPage,
                $currentPage, // ✅ WAJIB
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

        // Data Modal Survey
        $teamSurveys = [];
        foreach ($teams as $t) {
            $teamSurveys[$t->id] = $t->available_surveys ?? [];
        }

        return view('mitrabps.recommendation.index', compact(
            'paginatedMitras',
            'year',
            'month',
            'teams',
            'teamSurveys', // <--- Kirim $month
            'totalFiltered'
        ));
    }
}
