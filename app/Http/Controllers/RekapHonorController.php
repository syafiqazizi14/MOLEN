<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Placement;
use App\Models\Rate;
use App\Models\Team;
use App\Models\Mitra; // Tambahkan import Mitra
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapHonorExport;

class RekapHonorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. FILTER INPUT - Pastikan year adalah integer
        $year = (int) $request->input('year', date('Y'));
        $filterTeamId = $request->input('team_id');

        // 2. CEK OTORITAS
        $isAdmin = $user->is_mitra_admin == 1;
        $isLeader = !is_null($user->team_id);

        // 3. SIAPKAN DATA DROPDOWN TIM
        if ($isAdmin || !$isLeader) {
            // Jika Admin: Tampilkan semua pilihan tim
            $teams = Team::all();
        } else {
            // [UBAH DI SINI] Jika Ketua Tim: 
            // Ambil HANYA tim dia sendiri. 
            // (Opsi "Semua Tim" sudah ada otomatis di HTML View, jadi nanti hasilnya: "Semua Tim" + "Tim IPDS")
            $teams = Team::where('id', $user->team_id)->get();
        }

        // LOGIKA DEFAULT FILTER (Anti-Mental)
        if (!$isAdmin && $isLeader) {
            // Cek: Apakah baru pertama buka (URL bersih tanpa ?team_id=...)?
            if (!$request->has('team_id')) {
                // Jika baru buka, set default ke Tim Sendiri
                $filterTeamId = $user->team_id;
            }
            // TAPI, jika user sudah klik "Semua Tim" (ada ?team_id= tapi kosong),
            // kode ini akan dilewati, sehingga $filterTeamId tetap kosong (menampilkan semua data).
        }

        // 4. QUERY DATA PLACEMENT dengan casting yang tepat
        $query = Placement::with(['mitra', 'team'])
            ->whereRaw('CAST(year AS UNSIGNED) = ?', [$year]);

        if ($filterTeamId) {
            $query->where('team_id', $filterTeamId);
        }

        $placements = $query->get();

        // 5. AMBIL RATE HARGA
        $ratesData = Rate::whereRaw('CAST(year AS UNSIGNED) = ?', [$year])->get();
        $rates = [];
        foreach ($ratesData as $r) {
            $rates[$r->survey_name][$r->month] = $r->cost;
        }

        // 6. SUSUN STRUKTUR DATA MATRIX
        $rekapMitra = [];

        foreach ($placements as $p) {
            $mitraId = $p->mitra_id;
            $bulan = $p->month;

            // Inisialisasi Array Mitra jika belum ada
            if (!isset($rekapMitra[$mitraId])) {
                $rekapMitra[$mitraId] = [
                    'id' => $mitraId,
                    'nama' => $p->mitra->nama_lengkap ?? 'Unknown',
                    'sobat_id' => $p->mitra->sobat_id ?? '-',
                    'months' => [],
                    'grand_total' => 0
                ];
            }

            // Helper Hitung
            $hitung = function ($nama, $vol) use ($rates, $bulan) {
                if (!$nama) return 0;
                if (isset($rates[$nama][$bulan])) {
                    $harga = $rates[$nama][$bulan];
                } else {
                    $harga = 0;
                }
                return $vol * $harga;
            };

            // Hitung Subtotal
            $subtotal = 0;
            $details = [];

            if ($p->survey_1) {
                $h = $hitung($p->survey_1, $p->vol_1);
                $subtotal += $h;
                $details[] = ['nama' => $p->survey_1, 'vol' => $p->vol_1, 'honor' => $h];
            }
            if ($p->survey_2) {
                $h = $hitung($p->survey_2, $p->vol_2);
                $subtotal += $h;
                $details[] = ['nama' => $p->survey_2, 'vol' => $p->vol_2, 'honor' => $h];
            }
            if ($p->survey_3) {
                $h = $hitung($p->survey_3, $p->vol_3);
                $subtotal += $h;
                $details[] = ['nama' => $p->survey_3, 'vol' => $p->vol_3, 'honor' => $h];
            }

            // Masukkan ke Array Bulan Mitra
            if (!isset($rekapMitra[$mitraId]['months'][$bulan])) {
                $rekapMitra[$mitraId]['months'][$bulan] = [
                    'total_bulan' => 0,
                    'list_pekerjaan' => []
                ];
            }

            $rekapMitra[$mitraId]['months'][$bulan]['total_bulan'] += $subtotal;

            foreach ($details as $d) {
                $rekapMitra[$mitraId]['months'][$bulan]['list_pekerjaan'][] = $d;
            }

            $rekapMitra[$mitraId]['grand_total'] += $subtotal;
        }

        // Sortir berdasarkan Nama
        usort($rekapMitra, function ($a, $b) {
            return strcmp($a['nama'], $b['nama']);
        });

        return view('mitrabps.rekap.index', compact('rekapMitra', 'year', 'teams', 'filterTeamId', 'isAdmin'));
    }

    // =========================================================================
    // PERBAIKAN UTAMA ADA DI FUNGSI SHOW INI
    // =========================================================================
    public function show(Request $request, $id)
    {
        $year = (int) $request->input('year', date('Y'));
        $mitra = Mitra::findOrFail($id); // Menggunakan Model Mitra yang sudah di-use

        $placements = Placement::with(['team'])
            ->where('mitra_id', $id)
            ->whereRaw('CAST(year AS UNSIGNED) = ?', [$year])
            ->orderBy('month', 'asc')
            ->get()
            ->groupBy('month');

        // [PERBAIKAN ERROR "Array * Int"]
        // Kita gunakan pluck() agar format arraynya sederhana: ['NamaSurvei' => Harga]
        // Ini membuat View bisa langsung mengalikannya ($rates['Susenas'] * $vol)
        // Tanpa perlu pusing memikirkan index bulan.
        $rates = Rate::whereRaw('CAST(year AS UNSIGNED) = ?', [$year])
            ->pluck('cost', 'survey_name') // Key: survey_name, Value: cost
            ->toArray();

        return view('mitrabps.rekap.detail', compact('mitra', 'placements', 'year', 'rates'));
    }

    public function updateVol(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'placement_id' => 'required|exists:placements,id',
            'vol_1' => 'nullable|numeric',
            'vol_2' => 'nullable|numeric',
            'vol_3' => 'nullable|numeric',
            // 'force_save' => 'required' // Opsional, kita handle default 0 di bawah
        ]);

        try {
            $placement = Placement::findOrFail($request->placement_id);
            $user = Auth::user();

            // Cek Otoritas
            if (!$user->is_mitra_admin && $user->team_id != $placement->team_id) {
                return response()->json(['status' => 'error', 'message' => 'Anda tidak berhak mengubah data ini.'], 403);
            }

            // -----------------------------------------------------------------
            // A. HITUNG HONOR BARU UNTUK TUGAS INI (PREDIKSI)
            // -----------------------------------------------------------------

            // Helper function untuk ambil harga survei
            $getRate = function ($surveyName) use ($placement) {
                if (!$surveyName) return 0;
                // Ambil harga dari tabel rates (sesuaikan nama tabel jika perlu)
                $rate = \Illuminate\Support\Facades\DB::table('rates')
                    ->where('survey_name', $surveyName)
                    ->where('year', $placement->year)
                    // Cari yang bulannya sama, kalau tidak ada ambil sembarang di tahun itu
                    ->where(function ($q) use ($placement) {
                        $q->where('month', $placement->month)->orWhere('year', $placement->year);
                    })
                    ->orderBy('month', 'desc') // Prioritaskan bulan yg spesifik
                    ->value('cost');
                return $rate ?? 0;
            };

            $rate1 = $getRate($placement->survey_1);
            $rate2 = $getRate($placement->survey_2);
            $rate3 = $getRate($placement->survey_3);

            // Gunakan volume baru dari request (atau 0 jika kosong)
            $v1 = $request->vol_1 ?? 0;
            $v2 = $request->vol_2 ?? 0;
            $v3 = $request->vol_3 ?? 0;

            $newHonorThisTask = ($v1 * $rate1) + ($v2 * $rate2) + ($v3 * $rate3);

            // -----------------------------------------------------------------
            // B. HITUNG TOTAL HONOR TUGAS LAIN DI BULAN YG SAMA
            // -----------------------------------------------------------------
            $otherTasks = Placement::where('mitra_id', $placement->mitra_id)
                ->where('month', $placement->month)
                ->where('year', $placement->year)
                ->where('id', '!=', $placement->id) // Kecuali tugas ini
                ->get();

            $honorOtherTasks = 0;
            foreach ($otherTasks as $task) {
                // Hitung honor tugas lain
                $r1 = $getRate($task->survey_1);
                $r2 = $getRate($task->survey_2);
                $r3 = $getRate($task->survey_3);
                $honorOtherTasks += ($task->vol_1 * $r1) + ($task->vol_2 * $r2) + ($task->vol_3 * $r3);
            }

            $grandTotal = $honorOtherTasks + $newHonorThisTask;

            // -----------------------------------------------------------------
            // C. LOGIKA WARNING
            // -----------------------------------------------------------------
            $forceSave = $request->input('force_save', 0);

            if ($grandTotal > 4000000 && $forceSave == 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Total honor Mitra ini akan menjadi Rp ' . number_format($grandTotal, 0, ',', '.') . '. Melebihi batas 4 Juta. Lanjutkan simpan?',
                ]);
            }

            // -----------------------------------------------------------------
            // D. SIMPAN PERUBAHAN
            // -----------------------------------------------------------------
            $placement->vol_1 = $v1;
            $placement->vol_2 = $v2;
            $placement->vol_3 = $v3;
            $placement->save();

            return response()->json(['status' => 'success', 'message' => 'Volume berhasil diperbarui!']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        // [PERBAIKAN] Pastikan tahun adalah integer
        $tahun = (int) ($request->year ?? $request->tahun ?? date('Y'));
        
        // Baca 'month' atau 'bulan'
        $bulan = $request->month ?? $request->bulan;
        $bulan = !empty($bulan) ? (int) $bulan : null;

        $team_id = $request->team_id;
        $search = $request->search;

        $namaFile = 'Rekap_Honor_' . ($bulan ? $bulan . '_' : 'Tahun_') . $tahun . '.xlsx';

        return Excel::download(new RekapHonorExport($bulan, $tahun, $team_id, $search), $namaFile);
    }
}
