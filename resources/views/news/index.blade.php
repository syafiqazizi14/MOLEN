<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @include('viteall')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <link rel="icon" href="/Logo BPS.png" type="image/png">
    <title>NEWS - BPS 3516</title>
</head>

<body>
    @if (session('success'))
        <script>
            swal("Success!", "{{ session('success') }}", "success");
        </script>
    @endif

    @if ($errors->any())
        <script>
            swal("Error!", "{{ $errors->first() }}", "error");
        </script>
    @endif

    <div class="flex h-screen bg-gray-100 overflow-hidden" x-data="{ sidebarOpen: false }">
        <x-sidebar></x-sidebar>

        <div class="flex-1 flex flex-col overflow-hidden">
            <x-navbar></x-navbar>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="w-full max-w-[1700px] mx-auto px-3 sm:px-5 lg:px-6 py-8">
                    
                    <!-- Header Section - Premium Design -->
                    <div class="relative bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 rounded-3xl shadow-2xl p-8 md:p-10 mb-8 overflow-hidden">
                        <!-- Decorative Blobs -->
                        <div class="absolute top-0 right-0 w-72 h-72 bg-white rounded-full opacity-5 -mr-24 -mt-24"></div>
                        <div class="absolute bottom-0 left-1/3 w-48 h-48 bg-cyan-300 rounded-full opacity-10 blur-2xl -mb-16"></div>
                        <div class="absolute top-1/2 right-1/4 w-32 h-32 bg-indigo-300 rounded-full opacity-10 blur-xl"></div>
                        
                        <div class="relative flex justify-between items-center gap-4">
                            <div class="flex-1 flex items-center gap-5">
                                <!-- Icon Pill -->
                                <div class="bg-white/20 backdrop-blur-sm border border-white/30 p-3.5 rounded-full shadow-lg flex-shrink-0">
                                    <i class="fas fa-newspaper text-white text-2xl"></i>
                                </div>
                                
                                <div>
                                    <h1 class="text-3xl md:text-5xl font-black text-white leading-tight drop-shadow-sm">
                                        NEWS & UPDATES
                                    </h1>
                                    <p class="text-blue-100 text-base md:text-lg font-medium mt-1">
                                        Berita terbaru dan informasi penting
                                        <span class="font-bold text-white">BPS Kabupaten Mojokerto</span>
                                    </p>
                                </div>
                            </div>
                            
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('news.create') }}" 
                                   class="group bg-white hover:bg-blue-50 text-blue-700 px-7 py-3.5 rounded-full shadow-lg transition-all duration-300 flex items-center gap-3 font-bold hover:shadow-xl transform hover:scale-105 whitespace-nowrap text-base border border-white/60">
                                    <i class="fas fa-plus text-blue-600 group-hover:rotate-90 transition-transform duration-300"></i>
                                    <span>Berita Baru</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-6 gap-6 items-start">
                        
                        <!-- Main Content: News List (2/3 width) -->
                        <div class="lg:col-span-5 space-y-3">
                            @forelse($news as $item)
                                <div class="bg-white rounded-lg shadow-md hover:shadow-xl overflow-hidden transition-all duration-300 group border border-gray-100 flex gap-3 p-3">
                                    <!-- Image Left -->
                                    @if($item->image)
                                        <div class="w-52 h-32 flex-shrink-0 rounded-lg overflow-hidden">
                                            <img src="{{ asset('storage/uploads/news/' . $item->image) }}" 
                                                 alt="{{ $item->title }}"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        </div>
                                    @endif
                                    
                                    <!-- Content Right -->
                                    <div class="flex-1 flex flex-col justify-between">
                                        <div>
                                            <span class="inline-block text-xs font-bold text-red-600 bg-red-50 px-3 py-1 rounded-full mb-2">
                                                {{ strtoupper(substr($item->title, 0, 15)) }}
                                            </span>
                                            
                                            <h2 class="text-xl font-bold text-gray-900 mb-1.5 group-hover:text-blue-600 transition line-clamp-2">
                                                {{ $item->title }}
                                            </h2>
                                            
                                            <p class="text-sm text-gray-600 line-clamp-2 mb-2">
                                                {{ $item->content }}
                                            </p>
                                        </div>
                                        
                                        <!-- Footer with Actions -->
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <i class="far fa-clock text-blue-600"></i>
                                                {{ $item->created_at->diffForHumans() }}
                                            </span>
                                            @if(auth()->user()->is_admin)
                                                <div class="flex gap-2">
                                                    <a href="{{ route('news.edit', $item->id) }}" 
                                                       class="text-yellow-600 hover:text-yellow-700 hover:bg-yellow-100 p-1 rounded transition duration-300"
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('news.destroy', $item->id) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Yakin ingin menghapus berita ini?')"
                                                          style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="text-red-600 hover:text-red-700 hover:bg-red-100 p-1 rounded transition duration-300"
                                                                title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white rounded-lg shadow-md p-12 text-center border-2 border-dashed border-gray-300">
                                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                                    <h3 class="text-2xl font-bold text-gray-600 mb-2">Belum Ada Berita</h3>
                                    <p class="text-gray-500 mb-4">Belum ada berita yang dipublikasikan saat ini.</p>
                                    @if(auth()->user()->is_admin)
                                        <a href="{{ route('news.create') }}" 
                                           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-300">
                                            <i class="fas fa-plus mr-2"></i>Buat Berita Pertama
                                        </a>
                                    @endif
                                </div>
                            @endforelse

                            <!-- Pagination -->
                            @if($news->hasPages())
                                <div class="mt-6">
                                    <div class="flex justify-center">
                                        {{ $news->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right Sidebar: Birthday Widget Only -->
                        <div class="lg:col-span-1 self-start lg:justify-self-end">
                            <div class="sticky md:top-4 top-0">
                                <h3 class="text-lg font-black text-gray-900 mb-3 flex items-center gap-2">
                                    <span>🎂</span>
                                    <span>ULANG TAHUN</span>
                                </h3>
                                
                                <div class="space-y-3">
                                    @forelse($birthdays as $user)
                                        <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-[1.03] transition-all duration-300 cursor-pointer group border border-gray-100 w-full max-w-[220px] lg:ml-auto">
                                            @if($user->gambar)
                                                <div class="relative h-24 overflow-hidden">
                                                    <img src="{{ asset('storage/uploads/images/' . $user->gambar) }}" 
                                                         alt="{{ $user->name }}"
                                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                                </div>
                                            @else
                                                <div class="h-24 bg-gradient-to-br from-yellow-300 to-orange-400 flex items-center justify-center">
                                                    <span class="text-3xl font-bold text-white opacity-70">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                            @endif
                                            
                                            <div class="p-2.5 bg-gradient-to-r from-yellow-50 to-orange-50 border-t-4 border-yellow-400">
                                                <p class="text-gray-900 font-bold text-center text-sm line-clamp-2">
                                                    {{ $user->name }}
                                                </p>
                                                <p class="text-yellow-600 text-[11px] text-center mt-1 font-semibold">
                                                    Selamat Ulang Tahun! 🎉
                                                </p>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="bg-white rounded-lg shadow-md p-8 text-center border-2 border-dashed border-gray-300">
                                            <i class="fas fa-calendar-check text-5xl text-gray-300 mb-3"></i>
                                            <p class="text-gray-600 font-medium">Tidak ada yang ulang tahun hari ini</p>
                                            <p class="text-sm text-gray-500 mt-2">Ajak pegawai isi tanggal lahir di profil</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
