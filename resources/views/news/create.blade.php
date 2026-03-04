<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @include('viteall')
    <link rel="icon" href="/Logo BPS.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <title>Tambah Berita</title>
</head>

<body>
    @if ($errors->any())
        <script>
            swal("Error!", "{{ $errors->first() }}", "error");
        </script>
    @endif

    <a href="{{ route('news.index') }}" class="absolute left-0 top-0 bg-gray-700 text-white p-3 m-2 rounded-br-lg hover:bg-gray-900 transition z-10">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </a>

    <div class="bg-gray-100 min-h-screen flex items-center justify-center py-12 px-4">
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl w-full">
            <div class="mb-6 text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Tambah Berita Baru</h2>
                <p class="text-gray-600">Publikasikan berita atau informasi penting untuk semua pegawai</p>
            </div>

            <form action="{{ route('news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Title -->
                <div class="mb-5">
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Berita <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           placeholder="Masukkan judul berita..." 
                           class="w-full p-3 bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           value="{{ old('title') }}"
                           required>
                </div>

                <!-- Content -->
                <div class="mb-5">
                    <label for="content" class="block text-sm font-semibold text-gray-700 mb-2">
                        Isi Berita <span class="text-red-500">*</span>
                    </label>
                    <textarea id="content" 
                              name="content" 
                              rows="8"
                              placeholder="Tulis isi berita di sini..." 
                              class="w-full p-3 bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                              required>{{ old('content') }}</textarea>
                </div>

                <!-- Image Upload -->
                <div class="mb-6">
                    <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                        Gambar Berita <span class="text-gray-500 text-xs">(Opsional, Max: 5MB)</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label for="image" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span> atau drag & drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, JPEG atau GIF (Max 5MB)</p>
                            </div>
                            <input id="image" name="image" type="file" class="hidden" accept="image/*" onchange="previewImage(event)">
                        </label>
                    </div>
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-4 hidden">
                        <img id="preview" src="" alt="Preview" class="max-h-64 rounded-lg mx-auto shadow-md">
                        <button type="button" onclick="removeImage()" class="mt-2 text-red-600 hover:text-red-800 text-sm font-semibold">
                            <i class="fas fa-times"></i> Hapus Gambar
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-paper-plane mr-2"></i> Publikasikan
                    </button>
                    <a href="{{ route('news.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg shadow-lg transition-all duration-300">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            document.getElementById('image').value = '';
            document.getElementById('imagePreview').classList.add('hidden');
        }
    </script>
</body>

</html>
