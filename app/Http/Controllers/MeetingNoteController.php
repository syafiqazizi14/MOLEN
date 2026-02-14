<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\File;
use App\Models\User;
use App\Models\Schedule;
use App\Models\MeetingNote;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class MeetingNoteController extends Controller
{

   public function index(Request $request)
        {
            $perPage = 10;
        
            // Get the search input
            $search = $request->input('search');
        
            // Build the query
            $query = MeetingNote::query()
                ->when($search, function ($q) use ($search) {
                    $q->where('kegiatan', 'like', '%' . $search . '%');
                })
                ->orderBy('notulen', 'desc'); // Sort by notulen descending
        
            // Paginate the result
            $notes = $query->paginate($perPage)
                            ->withQueryString();
        
            // Transform for display
            $notes->getCollection()->transform(function ($note) {
                $user = User::find($note->user_id);
        
                return [
                    'kegiatan' => $note->kegiatan,
                    'notulen' => Carbon::parse($note->notulen)->translatedFormat('d F Y '),
                    'name' => $user ? $user->name : 'Tidak diketahui',
                    'file_path' => '/storage/uploads/docs/' . $note->filekelengkapan,
                    'id' => $note->id,
                ];
            });
        
            return view('agenkita.agenkitanotulen', ['notes' => $notes, 'pagination' => $notes]);
        }


   public function getEventsNotulen()
        {
            // Mengambil awal dan akhir bulan ini
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
        
             // Mengambil kegiatan yang dimulai dari awal hingga akhir bulan ini dan mengurutkan dari yang paling baru
            $schedules = Schedule::whereBetween('date_start', [$startOfMonth, $endOfMonth])
                ->orderBy('date_start', 'desc') // Urutkan dari yang paling baru
                ->get();
        
            // Membuat map dari kegiatan
            $events = $schedules->map(function ($event) {
                return [
                    'title' => $event->kegiatan,
                    'id' => $event->id,
                ];
            })->toArray();
        
            // Kirim data ke view
            return view('agenkita.agenkitaformnotulen', ['events' => $events]);
        }


    public function getNotulenById($id)
        {
            $event = MeetingNote::find($id);
        
            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }
        
            // ✅ Perbaikan: cari user berdasarkan user_id, bukan id notulen
            $user = User::find($event->user_id);
            $eventData = [
                'kegiatan' => $event->kegiatan,
                'notulen' => Carbon::parse($event->notulen)->translatedFormat('d F Y H:i'),
                'user_id' => $user ? $user->name : 'Tidak diketahui',
                'id' => $event->id,
            ];
        
            return response()->json($eventData);
        }


    public function getMeetingNotes()
    {
        $notulens = MeetingNote::all();
        $events = $notulens->map(function($event){
            return [
                'kegiatan' => $event->kegiatan,
                'name' => $event->name,
                'catatan' => $event->catatan,
                'id' => $event->id,
            ];
        });
        return response()->json($events);
    }

   public function store(Request $request): RedirectResponse
        {
            // Pastikan user sudah login
            if (!auth()->check()) {
                return redirect()->route('/')->with(['error' => 'Anda harus login terlebih dahulu!']);
            }
        
            // ✅ Validasi input
            $request->validate([
                'catatan' => 'nullable|string',
                'kegiatan' => 'required|exists:schedules,id',
                'notulensidate' => 'required|date',
                'notulensitime' => 'required',
                'dokumen' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip|max:10000', // max 10MB
            ]);
        
            $file = $request->file('dokumen');
            $filename = null;
        
            $idd = $request->kegiatan;
            $id_kegiatan = Schedule::find($idd);
            $nama_kegiatan = $id_kegiatan->kegiatan;
        
            // Format tanggal: ddmmyy
            $tanggalFormat = Carbon::parse($request->notulensidate)->format('dmy');
        
            // Slug kegiatan: biar aman dari spasi dan karakter aneh
            $slugKegiatan = Str::slug($nama_kegiatan);
        
            // Buat nama file baru: kegiatan_ddmmyy.pdf
            if ($file) {
                $extension = $file->getClientOriginalExtension();
                $baseFileName = $slugKegiatan . '_' . $tanggalFormat;
                $filename = $baseFileName . '.' . $extension;
        
                // Cek kalau nama file sudah ada
                if (Storage::exists('public/uploads/docs/' . $filename)) {
                    $filename = $baseFileName . '_' . time() . '.' . $extension;
                }
        
                $file->storeAs('public/uploads/docs', $filename);
            }
        
            MeetingNote::create([
                'user_id'         => auth()->id(),
                'catatan'         => $request->catatan,
                'schedule_id'     => $idd,
                'notulen'         => Carbon::parse($request->notulensidate . ' ' . $request->notulensitime),
                'kegiatan'        => $nama_kegiatan,
                'filekelengkapan' => $filename,
            ]);
        
            return redirect()->route('agenkitanotulen.index')->with(['success' => 'Data Berhasil Disimpan!']);
        }




  public function update(Request $request, $id): RedirectResponse
        {
            if (!auth()->check()) {
                return redirect()->route('/')->with(['error' => 'Anda harus login terlebih dahulu!']);
            }
        
            $request->validate([
                'catatan' => 'required|string|max:1000',
                'dokumen' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip|max:10000', // max 10MB
            ]);
        
            $meetingNote = MeetingNote::findOrFail($id);
        
            if ($request->hasFile('dokumen')) {
                // Hapus file lama jika ada
                if ($meetingNote->filekelengkapan && Storage::exists('public/uploads/docs/' . $meetingNote->filekelengkapan)) {
                    Storage::delete('public/uploads/docs/' . $meetingNote->filekelengkapan);
                }
        
                $file = $request->file('dokumen');
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file->getClientOriginalName());
                $file->storeAs('public/uploads/docs', $fileName);
                $meetingNote->filekelengkapan = $fileName;
            }
        
            $meetingNote->user_id = auth()->id();
            $meetingNote->catatan = $request->catatan;
            $meetingNote->save();
        
            return redirect()->route('agenkitanotulen.index')->with('success', 'Notulen berhasil diperbarui.');
        }


public function editFormNotulen($id)
    {
        // Mencari schedule berdasarkan ID
        $event = MeetingNote::find($id);

        // Jika event tidak ditemukan
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Mengembalikan data event dalam format JSON
        $eventData = [
            'kegiatan' => $event->kegiatan,
            // Pisahkan tanggal dan waktu dari 'notulen'
            'notulen_date' => Carbon::parse($event->notulen)->format('d/m/Y'), // Format: "10/08/2024"
            'notulen_time' => Carbon::parse($event->notulen)->format('H:i'),   // Format: "16:33"
            'schedule_id' => $event->schedule_id,
            'catatan' => $event->catatan,
            'id' => $event->id,
        ];


        // Kembali ke view dan mengirimkan data event
        return view('agenkita.agenkitaformeditnotulen', ['event' => $eventData]);
    }

public function delete($id)
{
    $notulen = MeetingNote::findOrFail($id);

    // ✅ Hapus file fisik jika ada
    if ($notulen->filekelengkapan && Storage::exists('public/uploads/docs/' . $notulen->filekelengkapan)) {
        Storage::delete('public/uploads/docs/' . $notulen->filekelengkapan);
    }

    $notulen->delete();

    return redirect()->back()->with('success', 'Notulen berhasil dihapus.');
}
}