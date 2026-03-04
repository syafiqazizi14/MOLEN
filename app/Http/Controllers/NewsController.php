<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource (available for all authenticated users).
     */
    public function index()
    {
        // Ambil semua berita, urutkan dari terbaru
        $news = News::with('creator')->orderBy('created_at', 'desc')->paginate(6);

        // Ambil user yang ulang tahun hari ini
        $birthdays = User::whereRaw('DAY(tanggal_lahir) = DAY(CURDATE())')
            ->whereRaw('MONTH(tanggal_lahir) = MONTH(CURDATE())')
            ->where('is_active', 1)
            ->get(['name', 'gambar', 'tanggal_lahir']);

        return view('news.index', compact('news', 'birthdays'));
    }

    /**
     * Show the form for creating a new resource (admin only).
     */
    public function create()
    {
        return view('news.create');
    }

    /**
     * Store a newly created resource in storage (admin only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // max 5MB
        ]);

        $image_name = null;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->storeAs('public/uploads/news', $image->hashName());
            $image_name = $image->hashName();
        }

        News::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $image_name,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('news.index')->with('success', 'Berita berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource (admin only).
     */
    public function edit($id)
    {
        $news = News::findOrFail($id);
        return view('news.edit', compact('news'));
    }

    /**
     * Update the specified resource in storage (admin only).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $news = News::findOrFail($id);

        $image_name = $news->image;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($news->image && Storage::exists('public/uploads/news/' . $news->image)) {
                Storage::delete('public/uploads/news/' . $news->image);
            }

            $image = $request->file('image');
            $image->storeAs('public/uploads/news', $image->hashName());
            $image_name = $image->hashName();
        }

        $news->update([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $image_name,
        ]);

        return redirect()->route('news.index')->with('success', 'Berita berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage (admin only).
     */
    public function destroy($id)
    {
        $news = News::findOrFail($id);

        // Delete image if exists
        if ($news->image && Storage::exists('public/uploads/news/' . $news->image)) {
            Storage::delete('public/uploads/news/' . $news->image);
        }

        $news->delete();

        return redirect()->route('news.index')->with('success', 'Berita berhasil dihapus!');
    }
}
