<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // [PENTING] Jangan lupa import ini
use App\Models\Mitra;
use App\Models\Team;
use App\Models\Placement;

class SurveyPlanController extends Controller
{
    // ... (Function index tetap sama, tidak perlu diubah) ...
    public function index(Request $request)
    {
        // ... paste kode index Anda yang lama di sini ...
        // (Saya persingkat agar muat, bagian ini sudah benar)

        $user = Auth::user();
        $year = $request->input('year', date('Y'));
        $search = $request->input('search');
        $filterTeamId = $request->input('team_id');
        if ($filterTeamId == 'all') $filterTeamId = null;

        $isAdmin = $user->role == 'admin' || $user->is_mitra_admin == 1;
        $isLeader = !$isAdmin && !is_null($user->team_id);
        $canEdit = $isAdmin || $isLeader;

        if ($isLeader) {
            $teams = Team::where('id', $user->team_id)->get();
            if (!$request->has('team_id')) $filterTeamId = $user->team_id;
        } else {
            $teams = Team::all();
        }

        $query = Mitra::query();
        $query->whereHas('placements', function ($q) use ($year, $filterTeamId) {
            $q->where('placements.year', $year);
            if ($filterTeamId) $q->where('placements.team_id', $filterTeamId);
        });

        $query->with(['placements' => function ($q) use ($year) {
            $q->where('placements.year', $year);
            $q->with('team');
        }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('sobat_id', 'like', "%{$search}%");
            });
        }

        $mitras = $query->orderBy('nama_lengkap', 'asc')->paginate(15)->withQueryString();

        $teamSurveys = [];
        foreach (Team::all() as $t) {
            $teamSurveys[$t->id] = $t->available_surveys ?? [];
        }

        $mitraLocks = [];
        try {
            $mitraLocks = DB::table('placements')
                ->where('year', $year)->where('status_anggota', 'Tetap')
                ->select('mitra_id', 'team_id')->get()
                ->groupBy('mitra_id')
                ->map(function ($items) {
                    return $items->pluck('team_id')->unique()->values()->all();
                })->toArray();
        } catch (\Exception $e) {
        }

        $userTeamId = $user->team_id;
        
        // Cek apakah ada parameter edit_placement untuk auto-open edit modal
        $editPlacement = null;
        if ($request->has('edit_placement')) {
            $editPlacement = Placement::with('mitra', 'team')->find($request->edit_placement);
        }

        return view('mitrabps.planning.index', [
            'mitras' => $mitras,
            'teams' => $teams,
            'year' => $year,
            'canEdit' => $canEdit,
            'teamSurveys' => $teamSurveys,
            'mitraLocks' => $mitraLocks,
            'userTeamId' => $userTeamId,
            'selectedTeam' => $filterTeamId,
            'filterTeamId' => $filterTeamId,
            'editPlacement' => $editPlacement
        ]);
    }

    // =========================================================================
    // 2. FUNCTION STORE (SIMPAN DATA)
    // =========================================================================
    public function store(Request $request)
    {
        $request->validate([
            'mitra_id' => 'required',
            'team_id'  => 'required',
            'month'    => 'required',
            'year'     => 'required',
            'survey_1' => 'required|string|max:255',
            'vol_1'    => 'required|numeric|min:1',
            'force_save' => 'nullable|in:0,1',
        ]);

        try {
            $rateData = DB::table('rates')
                ->where('survey_name', $request->survey_1)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();

            $costPerUnit = $rateData ? $rateData->cost : 0;

            // Hitung Honor
            $newHonor = $request->vol_1 * $costPerUnit;

            // B. HITUNG TOTAL EKSISTING
            $totalExistingHonor = 0;

            // Mengambil semua tugas lain di bulan yang sama
            $existingTasks = Placement::where('mitra_id', $request->mitra_id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->get();

            foreach ($existingTasks as $task) {
                $taskRate = DB::table('rates')
                    ->where('survey_name', $task->survey_1)
                    ->where('month', $task->month)
                    ->where('year', $task->year)
                    ->value('cost') ?? 0;

                $totalExistingHonor += ($task->vol_1 * $taskRate);
            }

            $grandTotal = $totalExistingHonor + $newHonor;

            // C. LOGIKA WARNING
            if ($grandTotal > 4000000 && $request->force_save == 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Total honor: Rp ' . number_format($grandTotal, 0, ',', '.') . '. Melebihi batas 4 Juta. Lanjutkan?',
                ]);
            }

            Placement::updateOrCreate(
                [
                    'mitra_id' => $request->mitra_id,
                    'team_id'  => $request->team_id,
                    'month'    => $request->month,
                    'year'     => $request->year,
                ],
                [
                    'survey_1' => $request->survey_1,
                    'vol_1'    => $request->vol_1,
                    'survey_2' => $request->filled('survey_2') ? $request->survey_2 : null,
                    'vol_2'    => $request->filled('survey_2') ? $request->input('vol_2', 1) : 1,
                    'survey_3' => $request->filled('survey_3') ? $request->survey_3 : null,
                    'vol_3'    => $request->filled('survey_3') ? $request->input('vol_3', 1) : 1,
                    'status_anggota' => 'Tetap',
                ]
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil disimpan'
                ]);
            }

            return back()->with('success', 'Data berhasil disimpan');
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error Server: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error Server: ' . $e->getMessage())->withInput();
        }
    }

    // =========================================================================
    // 3. FUNCTION UPDATE (EDIT DATA)
    // =========================================================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'survey_1' => 'required|string|max:255',
            'vol_1' => 'required|numeric|min:1',
        ]);

        try {
            $placement = Placement::findOrFail($id);

            // A. CARI HARGA
            $currentSurveyName = $placement->survey_1;

            $rateData = DB::table('rates')
                ->where('survey_name', $currentSurveyName)
                ->where('month', $placement->month)
                ->where('year', $placement->year)
                ->first();

            $costPerUnit = $rateData ? $rateData->cost : 0;
            $newHonorThisTask = $request->vol_1 * $costPerUnit;

            // B. HITUNG TOTAL LAIN (Kecuali tugas ini)
            $otherTasks = Placement::where('mitra_id', $placement->mitra_id)
                ->where('month', $placement->month)
                ->where('year', $placement->year)
                ->where('id', '!=', $id)
                ->get();

            $honorOtherTasks = 0;
            foreach ($otherTasks as $task) {
                $tRate = DB::table('rates')
                    ->where('survey_name', $task->survey_1)
                    ->where('month', $task->month)
                    ->where('year', $task->year)
                    ->value('cost') ?? 0;

                $honorOtherTasks += ($task->vol_1 * $tRate);
            }

            $grandTotal = $honorOtherTasks + $newHonorThisTask;

            // C. LOGIKA WARNING
            if ($grandTotal > 4000000 && $request->input('force_save', 0) == 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Total honor: Rp ' . number_format($grandTotal, 0, ',', '.') . '. Melebihi batas 4 Juta. Simpan perubahan?',
                ]);
            }

            // D. UPDATE
            $placement->survey_1 = $request->survey_1;
            $placement->vol_1 = $request->vol_1;
            $placement->survey_2 = $request->filled('survey_2') ? $request->survey_2 : null;
            $placement->vol_2 = $request->filled('survey_2') ? $request->input('vol_2', 1) : 1;
            $placement->survey_3 = $request->filled('survey_3') ? $request->survey_3 : null;
            $placement->vol_3 = $request->filled('survey_3') ? $request->input('vol_3', 1) : 1;

            if ($request->filled('team_id')) {
                $placement->team_id = $request->team_id;
            }

            $placement->save();

            return response()->json(['status' => 'success', 'message' => 'Data berhasil diperbarui']);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error Server (Baris ' . $e->getLine() . '): ' . $e->getMessage()
            ], 500);
        }
    }
}
