<?php

namespace App\Http\Controllers;

use App\Models\SuratMasukDisposisi;
use Carbon\Carbon;
use App\Models\Instansi;
use App\Models\Disposisi;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class SuratMasukController extends Controller
{
    //
    public function index(Request $request)
{
    $perPage = 10; // jumlah item per halaman

    $startDateInput = $request->input('start_date');
    $endDateInput   = $request->input('end_date');
    $searchKeyword  = $request->input('search');

    // Query dasar
    $query = SuratMasuk::query();

    // Filter tanggal
    if ($startDateInput && $endDateInput) {
        $startDate = Carbon::createFromFormat('m/d/Y', $startDateInput)->startOfDay();
        $endDate   = Carbon::createFromFormat('m/d/Y', $endDateInput)->endOfDay();

        $query->whereBetween('tglterima', [$startDate, $endDate]);
    }

    // Filter pencarian
    if ($searchKeyword) {
        $query->where(function ($q) use ($searchKeyword) {
            $q->where('perihal', 'like', "%{$searchKeyword}%")
              ->orWhere('namainstansi', 'like', "%{$searchKeyword}%");
        });
    }

    // Ambil data + pagination
    $suratmasuks = $query->orderBy('tglterima', 'desc')->paginate($perPage);

    // Tambahkan parameter filter ke pagination link
    $suratmasuks->appends([
        'start_date' => $startDateInput,
        'end_date'   => $endDateInput,
        'search'     => $searchKeyword,
    ]);

    // Format data untuk tampilan tabel
    $suratmasuks->getCollection()->transform(function ($suratmasuk) {
        $disposisiIds = SuratMasukDisposisi::where('suratmasuk_id', $suratmasuk->id)->pluck('disposisi_id');
        $nama_disposisi = Disposisi::whereIn('id', $disposisiIds)->pluck('namadisposisi')->toArray();
        $nama_disposisi_string = implode(', ', $nama_disposisi);

        return [
            'perihal'      => $suratmasuk->perihal,
            'tglterima'    => Carbon::parse($suratmasuk->tglterima)->translatedFormat('d F Y'),
            'namadisposisi'=> $nama_disposisi_string,
            'namainstansi' => $suratmasuk->namainstansi,
            'nosurat'      => $suratmasuk->nosurat,
            'id'           => $suratmasuk->id,
        ];
    });

    return view('hamukti.hamuktisuratmasuk', [
        'suratmasuks' => $suratmasuks,
        'pagination'  => $suratmasuks,
    ]);
}
    public function suratMasukFormEdit($id)
    {

        $suratmasuk = SuratMasuk::find($id);

        // Jika event tidak ditemukan
        if (!$suratmasuk) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $surat = [
            'tglterima' => Carbon::parse($suratmasuk->tglterima)->format('m-d-Y'),
            'tglsurat' => Carbon::parse($suratmasuk->tglsurat)->format('m-d-Y'),
            'nosurat' => $suratmasuk->nosurat,
            'namainstansi' => $suratmasuk->namainstansi,
            'perihal' => $suratmasuk->perihal,
            'id' => $suratmasuk->id,

        ];

        $instansis = Instansi::all();
        // Membuat map dari kegiatan
        $instansis = $instansis->map(function ($instansi) {
            return [
                'namasingkat' => $instansi->namasingkat,

            ];
        })->toArray(); // Mengubah hasil map menjadi array

        $disposisis = Disposisi::all();
        // Membuat map dari kegiatan
        $disposisis = $disposisis->map(function ($disposisi) {
            return [
                'namadisposisi' => $disposisi->namadisposisi,
                'id' => $disposisi->id,
            ];
        })->toArray(); // Mengubah hasil map menjadi array

        // Kirim data ke view
        return view('hamukti.hamuktisuratmasukformedit', ['disposisis' => $disposisis, 'surat' => $surat, 'instansis' => $instansis]);
    }
    public function suratMasukForm()
    {

        $instansis = Instansi::all();
        // Membuat map dari kegiatan
        $instansis = $instansis->map(function ($instansi) {
            return [
                'namasingkat' => $instansi->namasingkat,
                'id' => $instansi->id,
            ];
        })->toArray(); // Mengubah hasil map menjadi array

        $disposisis = Disposisi::all();
        // Membuat map dari kegiatan
        $disposisis = $disposisis->map(function ($disposisi) {
            return [
                'namadisposisi' => $disposisi->namadisposisi,
                'id' => $disposisi->id,
            ];
        })->toArray(); // Mengubah hasil map menjadi array

        // Kirim data ke view
        return view('hamukti.hamuktisuratmasukform', ['disposisis' => $disposisis, 'instansis' => $instansis]);
    }
    public function getSuratMasukById($id)
    {
        // Mencari presence berdasarkan ID
        $suratmasuk = SuratMasuk::find($id);

        // Jika event tidak ditemukan
        if (!$suratmasuk) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Mengembalikan data event dalam format JSON
        $suratmasukData = [
            'perihal' => $suratmasuk->perihal,
            'tglterima' => Carbon::parse($suratmasuk->tglterima)->translatedFormat('d F Y'), // Menjadi format lokal "09 Oktober 2024 00:56:25"
            'file' => $suratmasuk->file,
            'namainstansi' => $suratmasuk->namainstansi,
            'nosurat' => $suratmasuk->nosurat,
            'id' => $suratmasuk->id,
        ];
        // dd($suratmasukData->tglterima);
        return response()->json($suratmasukData);
    }
    public function store(Request $request): RedirectResponse
{
    // Pastikan user sudah login
    if (!auth()->check()) {
        return redirect()->route('/')->with(['error' => 'Anda harus login terlebih dahulu!']);
    }

    // Format tanggal terima untuk nama file
    $tglTerimaFormatted = Carbon::createFromFormat('m/d/Y', $request->tglterima)->format('dmY');
    // Sanitasi perihal agar aman untuk nama file
    $perihalSanitized = preg_replace('/[^A-Za-z0-9_\- ]/', '', $request->perihal);
    $perihalSanitized = str_replace(' ', '_', $perihalSanitized); // ubah spasi jadi underscore

    $file_name = null;

    // Upload file
    if ($request->hasFile('dokumen')) {
        $file = $request->file('dokumen');
        $extension = $file->getClientOriginalExtension();

        // Bentuk nama file baru berdasarkan tanggal terima dan perihal
        $file_name = "{$tglTerimaFormatted}_{$perihalSanitized}.{$extension}";

        // Simpan file ke storage
        $file->storeAs('public/uploads/docs', $file_name);
    }

    $nama_instansi = $request->instansi;

    // Simpan data ke database
    $surat = SuratMasuk::create([
        'perihal'         => $request->perihal,
        'namainstansi'    => $nama_instansi,
        'tglterima'       => Carbon::createFromFormat('m/d/Y', $request->tglterima)->format('Y-m-d'),
        'tglsurat'        => Carbon::createFromFormat('m/d/Y', $request->tglsurat)->format('Y-m-d'),
        'nosurat'         => $request->nosurat,
        'disposisi_id'    => $request->disposisi,
        'uraiandisposisi' => $request->uraiandisposisi,
        'file'            => $file_name,
    ]);

    $surat->disposisi()->sync($request->disposisi);

    return redirect()->route('hamuktisuratmasuk.index')->with(['success' => 'Data Berhasil Disimpan!']);
}
    
    public function delete($id)
    {
        $suratmasuk = SuratMasuk::findOrFail($id);
        $suratmasuk->delete();

        // return redirect('agenkitaagenda.getEvents')->with('success', 'Presensi berhasil dihapus.');
        return redirect('/hamuktisuratmasuk')->with('success', 'Presensi berhasil dihapus.');
    }

 public function update(Request $request, $id)
{
    $suratmasuk = SuratMasuk::findOrFail($id);

    // Format tanggal terima untuk nama file
    $tglTerimaFormatted = Carbon::createFromFormat('m/d/Y', $request->tglterima)->format('dmY');

    // Sanitasi perihal agar aman untuk nama file
    $perihalSanitized = preg_replace('/[^A-Za-z0-9_\- ]/', '', $request->perihal);
    $perihalSanitized = str_replace(' ', '_', $perihalSanitized);

    // Default: gunakan file lama
    $newFile = $suratmasuk->file;

    // Proses upload file baru (jika ada)
    if ($request->hasFile('dokumen')) {
        $file = $request->file('dokumen');
        $extension = $file->getClientOriginalExtension();

        // Buat nama file baru berdasarkan tgl terima + perihal
        $file_name = "{$tglTerimaFormatted}_{$perihalSanitized}.{$extension}";

        // Hapus file lama jika ada
        if ($suratmasuk->file && Storage::exists('public/uploads/docs/' . $suratmasuk->file)) {
            Storage::delete('public/uploads/docs/' . $suratmasuk->file);
        }

        // Simpan file baru
        $file->storeAs('public/uploads/docs', $file_name);

        $newFile = $file_name;
    }

    // Update data surat masuk
    $suratmasuk->update([
        'perihal'          => $request->perihal,
        'tglterima'        => Carbon::createFromFormat('m/d/Y', $request->tglterima)->format('Y-m-d'),
        'uraiandisposisi'  => $request->uraiandisposisi,
        'file'             => $newFile, // gunakan kolom yang benar
    ]);

    // Update relasi disposisi
    $suratmasuk->disposisi()->sync($request->disposisi);

    return redirect()->route('hamuktisuratmasuk.index')->with(['success' => 'Data Berhasil Diupdate!']);
}
}
