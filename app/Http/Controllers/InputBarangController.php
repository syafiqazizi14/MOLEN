<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Barang;
use App\Models\InputBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InputBarangController extends Controller
{
    //
    public function inputBarangForm($id)
    {

        $barangs = Barang::find($id);

        // Jika event tidak ditemukan
        if (!$barangs) {
            return response()->json(['message' => 'Event not found'], 404);
        }
        $barang = [
            'namabarang' => $barangs->namabarang,
            'stoktersedia' => $barangs->stoktersedia,
            'id' => $barangs->id,
        ];
        // Kirim data ke view
        return view('siminbar.siminbarinputbarangform', ['barang' => $barang]);
    }

    public function store(Request $request){
        if (!auth()->check()) {
            return redirect()->route('/')->with(['error' => 'Anda harus login terlebih dahulu!']);
        }
        $barang = Barang::find($request->id);
        $jml = $barang->stoktersedia;
        $barang->update([
            'stoktersedia' => $jml+$request->jumlahtambah,
        ]);
        InputBarang::create([
            'tanggal' => Carbon::parse($request->inputdate . ' ' . $request->inputtime),
            'jumlahtambah' => $request->jumlahtambah,
            'barang_id' => $request->id,
        ]);
        return redirect('siminbardaftarbarang')->with(['Stok barang telah ditambahkan'],200);
    }
    
    public function update(Request $request, $id)
        {
            $validated = $request->validate([
                'jumlahtambah' => ['required','integer','min:0'],
                'stoktersedia' => ['required','integer','min:0'],
            ]);
        
            $item = InputBarang::findOrFail($id);
            $item->jumlahtambah = $validated['jumlahtambah'];
            $item->stoktersedia = $validated['stoktersedia'];
            $item->save();
        
            return response()->json(['status' => 'ok']);
        }
      public function index(Request $request)
{
    $q = InputBarang::query()
        ->select([
            'inputbarangs.id',
            'inputbarangs.tanggal',
            'inputbarangs.jumlahtambah',
            'barangs.namabarang',
            'barangs.stoktersedia',
        ])
        ->join('barangs', 'barangs.id', '=', 'inputbarangs.barang_id');

    // Search by nama barang (opsional)
    if ($s = trim($request->input('search', ''))) {
        $q->where('barangs.namabarang', 'like', "%{$s}%");
    }

    // ----- Filter tanggal yang akurat (half-open range) -----
    // Ambil raw string agar fleksibel; parse ke Carbon lalu normalisasi
    $startRaw = $request->input('start_date');
    $endRaw   = $request->input('end_date');

    $start = $startRaw ? \Carbon\Carbon::parse($startRaw)->startOfDay() : null;
    $endEx = $endRaw   ? \Carbon\Carbon::parse($endRaw)->startOfDay()->addDay() : null; // eksklusif

    // Tukar jika user kebalik (start > end)
    if ($start && $endEx && $start->gte($endEx)) {
        [$start, $endEx] = [$endEx->copy()->subDay()->startOfDay(), $start->copy()->addDay()->startOfDay()];
    }

    if ($start && $endEx) {
        $q->where('inputbarangs.tanggal', '>=', $start)
          ->where('inputbarangs.tanggal', '<',  $endEx);
    } elseif ($start) {
        $q->where('inputbarangs.tanggal', '>=', $start);
    } elseif ($endEx) {
        $q->where('inputbarangs.tanggal', '<',  $endEx);
    }
    // ---------------------------------------------------------

    $items = $q->orderByDesc('inputbarangs.tanggal')
               ->paginate(15)
               ->withQueryString(); // bawa search & tanggal saat pindah halaman

    return view('siminbar.rekapinputbarangadmin', compact('items'));
}


    // DETAIL buat modal
    public function show($id)
    {
        $row = InputBarang::with('barang')->findOrFail($id);

        return response()->json([
            'id'            => $row->id,
            'tanggal'       => $row->tanggal,
            'namabarang'    => $row->barang?->namabarang,
            'jumlahtambah'  => (int) $row->jumlahtambah,
            'stoktersedia'  => (int) $row->barang?->stoktersedia,
        ]);
    }

    // INLINE UPDATE dari tombol save
    public function inlineUpdate(Request $request, $id)
    {
        $data = $request->validate([
            'jumlahtambah' => ['required', 'integer', 'min:0'],
            'stoktersedia' => ['required', 'integer', 'min:0'],
        ]);

        $row = InputBarang::with('barang')->findOrFail($id);

        DB::transaction(function () use ($row, $data) {
            // update kolom di tabel inputbarangs
            $row->update([
                'jumlahtambah' => $data['jumlahtambah'],
            ]);

            // update kolom di tabel barangs (relasi)
            if ($row->barang) {
                $row->barang->update([
                    'stoktersedia' => $data['stoktersedia'],
                ]);
            }
        });

        return response()->json([
            'message'       => 'Perubahan disimpan.',
            'jumlahtambah'  => (int) $data['jumlahtambah'],
            'stoktersedia'  => (int) $data['stoktersedia'],
        ]);
    }
}
