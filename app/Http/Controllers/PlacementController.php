<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mitra;
use App\Models\Team;
use App\Models\Placement;
use Illuminate\Support\Facades\DB;

class PlacementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Filter Input
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $search = $request->input('search');

        // 2. Otoritas
        $isAdmin = $user->is_mitra_admin == 1;
        $isLeader = !is_null($user->team_id);

        $canEdit = $isAdmin || $isLeader;

        // 3. Data Tim (Kecualikan Admin ID 1)
        $teams = Team::where('id', '!=', 1)->get();

        // 4. Query Mitra UTAMA (Dashboard Tabel)
        // Logika: Tampilkan SEMUA mitra yang MEMILIKI TUGAS di bulan/tahun ini.
        // Kita TIDAK memfilter 'jenis_mitra' di sini agar jika ada mitra Non-Rutin 
        // yang terlanjur punya tugas, datanya TETAP MUNCUL di tabel.

        $query = Mitra::whereHas('placements', function ($q) use ($month, $year, $request) {
            $q->where('month', $month)->where('year', $year);

            // Filter Tim (Jika user memilih dropdown tim di dashboard)
            if ($request->filled('filter_team_id')) {
                $q->where('team_id', $request->filter_team_id);
            }
        });

        // Eager Loading (Ambil detail tugas)
        $query->with(['placements' => function ($q) use ($month, $year, $request) {
            $q->where('month', $month)->where('year', $year)->with('team');

            if ($request->filled('filter_team_id')) {
                $q->where('team_id', $request->filter_team_id);
            }
        }]);

        // Pencarian Nama di Tabel
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('sobat_id', 'like', "%{$search}%");
            });
        }

        $mitras = $query->orderBy('nama_lengkap', 'asc')
            ->paginate(10)
            ->withQueryString();

        // 5. Data Pendukung Modal (JSON Survei)
        $teamSurveys = [];
        foreach (Team::all() as $t) {
            $teamSurveys[$t->id] = $t->available_surveys ?? [];
        }

        // --- [PERUBAHAN UTAMA DI SINI] ---
        // 6. Dropdown Pencarian Mitra (TomSelect) untuk Modal TAMBAH
        // Hanya ambil Mitra yang statusnya 'Rutin'

        $mitraList = Mitra::where('jenis_mitra', 'Rutin') // <--- FILTER INI KUNCINYA
            ->select('id_mitra', 'nama_lengkap', 'sobat_id')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        // 7. DATA KUNCI TIM (Validasi 2 Tim)
        $mitraLocks = DB::table('placements')
            ->where('year', $year)
            ->where('status_anggota', 'Tetap')
            ->select('mitra_id', 'team_id')
            ->get()
            ->groupBy('mitra_id')
            ->map(function ($items) {
                return $items->pluck('team_id')->unique()->values()->all();
            })
            ->toArray();

        return view('mitrabps.penempatan.penempatanMitra', compact(
            'mitras',
            'teams',
            'month',
            'year',
            'canEdit',
            'isAdmin',
            'user',
            'teamSurveys',
            'mitraList', // Ini sekarang hanya berisi Mitra Rutin
            'mitraLocks'
        ));
    }

    // --- FUNCTION STORE (SIMPAN BARU) ---
            public function store(Request $request)
            {
                $request->validate([
                    'mitra_id' => 'required',
                    'team_id'  => 'required',
                    'year'     => 'required',
                    'month'    => 'required',
                    'survey_1' => 'required',
                ]);
            
                $status = $request->input('status_anggota', 'Tetap');
            
                // ...validasi & updateOrCreate kamu...
            
                Placement::updateOrCreate(
                    [
                        'mitra_id' => $request->mitra_id,
                        'team_id'  => $request->team_id,
                        'month'    => $request->month,
                        'year'     => $request->year,
                    ],
                    [
                        'survey_1' => $request->survey_1,
                        'vol_1'    => $request->vol_1 ?? 1,
                        'survey_2' => $request->filled('survey_2') ? $request->survey_2 : null,
                        'vol_2'    => $request->filled('survey_2') ? ($request->input('vol_2', 0)) : 0,
                        'survey_3' => $request->filled('survey_3') ? $request->survey_3 : null,
                        'vol_3'    => $request->filled('survey_3') ? ($request->input('vol_3', 0)) : 0,
                        'status_anggota' => $status
                    ]
                );
            
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Penugasan berhasil disimpan!'
                    ]);
                }
            
                return back()->with('success', 'Penugasan berhasil disimpan!');
            }


    // --- FUNCTION UPDATE ---
        public function update(Request $request, $id)
        {
            $placement = Placement::findOrFail($id);
            $user = Auth::user();
        
            if (!$user->is_mitra_admin && $user->team_id && $placement->team_id != $user->team_id) {
                $msg = 'Akses Ditolak. Bukan tim Anda.';
                return $request->expectsJson()
                    ? response()->json(['status' => 'error', 'message' => $msg], 403)
                    : back()->with('error', $msg);
            }
        
            $request->validate([
                'survey_1' => 'required|string|max:255',
                'vol_1'    => 'required|numeric|min:1',
            ]);
        
            try {
                $placement->update([
                    'survey_1' => $request->survey_1,
                    'vol_1'    => $request->vol_1,
                    'survey_2' => $request->filled('survey_2') ? $request->survey_2 : null,
                    'vol_2'    => $request->filled('survey_2') ? $request->vol_2 : 0,
                    'survey_3' => $request->filled('survey_3') ? $request->survey_3 : null,
                    'vol_3'    => $request->filled('survey_3') ? $request->vol_3 : 0,
                ]);
        
                if ($request->expectsJson()) {
                    return response()->json(['status' => 'success', 'message' => 'Penugasan berhasil diperbarui!']);
                }
        
                return back()->with('success', 'Penugasan berhasil diperbarui!');
            } catch (\Exception $e) {
                $msg = 'Gagal update: ' . $e->getMessage();
                return $request->expectsJson()
                    ? response()->json(['status' => 'error', 'message' => $msg], 500)
                    : back()->with('error', $msg);
            }
        }
                public function destroy($id)
                    {
                        $placement = \App\Models\Placement::findOrFail($id);
                        $user = \Illuminate\Support\Facades\Auth::user();
                    
                        $isAdmin = ($user->role === 'admin') || ($user->is_mitra_admin == 1);
                    
                        if (!$isAdmin) {
                            if (is_null($user->team_id) || $placement->team_id != $user->team_id) {
                                return back()->with('error', 'Akses Ditolak. Bukan tim Anda.');
                            }
                        }
                    
                        $placement->delete();
                        return back()->with('success', 'Data penempatan dihapus.');
                    }

        
}
