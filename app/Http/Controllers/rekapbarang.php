<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class rekapbarang extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

   public function exportPdf(Request $request)
{
    $user = Auth::user();
    if (!($user && ($user->is_admin == 1 || $user->jabatan === 'Kasubag Umum'))) {
        abort(403, 'Anda tidak memiliki akses.');
    }

    $search    = trim((string) $request->query('search', ''));
    $startRaw  = $request->query('start_date');
    $endRaw    = $request->query('end_date');

    // Parser fleksibel → kembalikan string 'Y-m-d' (tanpa waktu)
    $toYmd = function ($v) {
        if (!$v) return null;
        $v = urldecode($v);
        $fmts = ['m/d/Y', 'd/m/Y', 'Y-m-d', 'd-m-Y', 'm-d-Y'];
        foreach ($fmts as $f) {
            try {
                return \Carbon\Carbon::createFromFormat($f, $v)->format('Y-m-d');
            } catch (\Exception $e) {}
        }
        try {
            return \Carbon\Carbon::parse($v)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    };

    $sdYmd = $toYmd($startRaw);
    $edYmd = $toYmd($endRaw);

    // Jika user pilih satu hari yang sama, tetap oke. Jika start > end, tukar.
    if ($sdYmd && $edYmd && $sdYmd > $edYmd) {
        [$sdYmd, $edYmd] = [$edYmd, $sdYmd];
    }

    // Query: join ke barangs
    $q = \DB::table('inputbarangs as i')
        ->join('barangs as b', 'b.id', '=', 'i.barang_id')
        ->select([
            'i.id',
            'i.tanggal',
            'i.jumlahtambah',
            \DB::raw('b.namabarang as namabarang'),
            \DB::raw('COALESCE(b.stoktersedia,0) as stoktersedia'),
        ]);

    if ($search !== '') {
        $q->where('b.namabarang', 'like', "%{$search}%");
    }

    // === Filter tanggal level DATE (hindari masalah timezone) ===
    if ($sdYmd && $edYmd) {
        $q->whereBetween(\DB::raw('DATE(i.tanggal)'), [$sdYmd, $edYmd]);
    } elseif ($sdYmd) {
        $q->whereDate('i.tanggal', '>=', $sdYmd);
    } elseif ($edYmd) {
        $q->whereDate('i.tanggal', '<=', $edYmd);
    }

    $items = $q->orderBy('i.tanggal', 'asc')->get();

    return view('siminbar.rekapinputbarangadmin_print', [
        'items'     => $items,
        'search'    => $search,
        'startDate' => $sdYmd,   // kirim balik versi normalisasi (opsional)
        'endDate'   => $edYmd,
    ]);
}
}
