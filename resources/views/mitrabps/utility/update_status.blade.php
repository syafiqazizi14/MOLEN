@php
    $title = 'Update Status Mitra';
@endphp

@include('mitrabps.headerTemp')

<body class="bg-gray-100">
    <div class="flex h-screen">
        <x-sidebar></x-sidebar>

        <div class="flex-1 flex flex-col overflow-hidden">
            <x-navbar></x-navbar>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
                    <div class="border-b pb-4 mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Update Status Mitra (Massal)
                        </h2>
                        <p class="text-gray-500 text-sm">Gunakan fitur ini untuk menandai Mitra Rutin atau Sensus secara
                            cepat.</p>
                    </div>

                    {{-- 1. ERROR VALIDASI (Wajib Ada agar tau kenapa gagal) --}}
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                            <p class="font-bold">Gagal Upload:</p>
                            <ul class="list-disc ml-5 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- 2. NOTIFIKASI SUKSES / GAGAL DARI CONTROLLER --}}
                    @if (session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                            <i class="bi bi-check-circle-fill mr-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                            <i class="bi bi-exclamation-triangle-fill mr-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded mb-6 text-sm">
                        <p class="font-bold"><i class="bi bi-info-circle"></i> Cara Penggunaan:</p>
                        <ol class="list-decimal ml-5 mt-1 space-y-1">
                            <li>Buat file Excel baru.</li>
                            <li>Isi <strong>Kolom A</strong> dengan daftar <strong>SOBAT ID</strong> mitra (Angka Saja).
                            </li>
                            <li>Pastikan Sobat ID ada jika ada mitra Rutin yang sama dengan tahun ini dan tahun lalu
                            </li>
                            <li>Simpan file dengan format <strong>CSV (Comma delimited)</strong>.</li>
                            <li>Upload file tersebut di bawah ini.</li>
                        </ol>
                    </div>

                    <form action="{{ route('mitra.status.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Set Status Menjadi:</label>
                                <select name="status_target"
                                    class="w-full border border-gray-300 p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                                    <option value="Rutin">Set sebagai Mitra Rutin (Tampil di Pencarian)</option>
                                    <option value="Sensus">Set sebagai Mitra Sensus (Sembunyi)</option>
                                    <option value="">Hapus Status (Reset ke Normal / NULL)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-bold mb-2">File CSV (List Sobat ID)</label>
                                <input type="file" name="file_csv" required
                                    class="w-full border border-gray-300 p-2 rounded bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <span class="text-xs text-gray-500">*Hanya menerima file .csv</span>
                            </div>

                            <div class="flex justify-between items-center pt-4">
                                {{-- Ganti route ini ke halaman penempatan (bukan rekap) agar alurnya lebih enak --}}
                                <a href="{{ url('/mitra/penempatan') }}"
                                    class="text-gray-500 hover:text-gray-700 font-semibold text-sm">
                                    &larr; Kembali ke Penempatan
                                </a>
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition flex items-center gap-2">
                                    <i class="bi bi-upload"></i> Proses Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
