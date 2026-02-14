@php $title = 'Rekap Honor Tahunan'; @endphp
@include('mitrabps.headerTemp')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    /* Agar header tabel tetap di atas saat scroll ke bawah */
    thead th {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    /* Agar kolom nama tetap di kiri saat scroll ke samping */
    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 20;
        background-color: #fff;
    }

    .sticky-header-col {
        position: sticky;
        left: 0;
        z-index: 30;
        background-color: #f3f4f6;
    }
</style>
@include('mitrabps.cuScroll')

<body class="bg-gray-100">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen">
        <x-sidebar></x-sidebar>
        <div class="flex flex-col flex-1 overflow-hidden">
            <x-navbar></x-navbar>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">Rekap Honor Mitra Tahun {{ $year }}</h3>
                    <div class="flex gap-2">
                        @if (Auth::user()->team_id && !Auth::user()->is_mitra_admin)
                            <a href="{{ route('mitra.rates.index') }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm flex items-center gap-2 shadow transition transform hover:scale-105">
                                <i class="bi bi-gear-fill"></i> Atur Honor
                            </a>
                        @endif
                        <a href="{{ route('mitra.planning.index') }}"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded shadow flex items-center gap-2 text-sm">
                            <i class="bi bi-calendar-week"></i> Perencanaan
                        </a>

                        <a href="#" id="exportBtn"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow inline-flex items-center">
                            <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
                        </a>

                        <a href="{{ route('mitra.penempatan.index') }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm flex items-center gap-2">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="bg-white p-4 rounded shadow mb-6">
                    <form method="GET" action="{{ route('mitra.rekap.index') }}"
                        class="flex flex-col md:flex-row items-center gap-4">

                        <div class="flex items-center gap-2">
                            <label class="font-bold text-gray-700 text-sm">Tim:</label>
                            <select name="team_id" onchange="this.form.submit()"
                                class="border p-2 rounded text-sm min-w-[200px] shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Tim</option>
                                @foreach ($teams as $t)
                                    <option value="{{ $t->id }}" {{ $filterTeamId == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="font-bold text-gray-700 text-sm">Tahun:</label>
                            <select name="year" onchange="this.form.submit()"
                                class="border p-2 rounded text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @foreach (range(2023, 2030) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="font-bold text-gray-700 text-sm">Bulan Export:</label>
                            <select name="month" id="monthFilter"
                                class="border p-2 rounded text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Bulan (Tahunan)</option>
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </form>
                </div>

                <div class="bg-white rounded shadow overflow-hidden flex flex-col h-[600px]">
                    <div class="overflow-auto flex-1">
                        <table class="min-w-max border-collapse">
                            <thead class="bg-gray-100 text-gray-600 text-xs uppercase leading-normal">
                                <tr>
                                    <th
                                        class="py-3 px-4 text-left border-b border-r sticky-header-col shadow-sm min-w-[200px]">
                                        Nama Mitra
                                    </th>
                                    @foreach (range(1, 12) as $m)
                                        <th class="py-3 px-2 text-center border-b min-w-[150px]">
                                            {{ date('M', mktime(0, 0, 0, $m, 1)) }}
                                        </th>
                                    @endforeach
                                    <th class="py-3 px-4 text-center border-b bg-yellow-50 min-w-[120px]">
                                        Total {{ $year }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-xs">
                                @forelse($rekapMitra as $mitra)
                                    <tr class="border-b hover:bg-gray-50 transition duration-150">

                                        <td class="py-3 px-4 text-left border-r sticky-col align-top shadow-sm">
                                            <div class="text-sm">
                                                <a href="{{ route('mitra.rekap.show', $mitra['id']) }}?year={{ $year }}"
                                                    class="font-bold text-gray-900 hover:text-blue-900 hover:underline"
                                                    title="Klik untuk melihat detail 12 bulan">
                                                    {{ $mitra['nama'] }}
                                                </a>
                                            </div>

                                            <div class="text-xs text-gray-400 mt-1">
                                                {{ $mitra['sobat_id'] }}
                                            </div>
                                        </td>

                                        @foreach (range(1, 12) as $m)
                                            <td class="py-2 px-2 text-center align-top border-r h-full">
                                                @if (isset($mitra['months'][$m]))
                                                    @php $bulanData = $mitra['months'][$m]; @endphp

                                                    <div class="flex flex-col h-full justify-between">
                                                        <div class="space-y-1 mb-2">
                                                            @foreach ($bulanData['list_pekerjaan'] as $job)
                                                                <div
                                                                    class="bg-blue-50 border border-blue-100 rounded p-1 text-left">
                                                                    <div class="font-bold text-blue-700 truncate"
                                                                        title="{{ $job['nama'] }}">
                                                                        {{ Str::limit($job['nama'], 18) }}
                                                                    </div>
                                                                    <div
                                                                        class="flex justify-between text-[10px] text-gray-500">
                                                                        <span>Vol: {{ $job['vol'] }}</span>
                                                                        <span>Rp
                                                                            {{ number_format($job['honor'] / 1000) }}k</span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <div
                                                            class="font-bold text-green-700 border-t pt-1 mt-1 bg-green-50 rounded">
                                                            Rp {{ number_format($bulanData['total_bulan']) }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-200">-</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td
                                            class="py-3 px-4 text-right font-bold text-gray-900 bg-yellow-50 align-middle border-l">
                                            Rp {{ number_format($mitra['grand_total']) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="py-8 text-center text-gray-500">
                                            Belum ada data honor di tahun {{ $year }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        // Update export URL based on month filter
        document.addEventListener('DOMContentLoaded', function() {
            const exportBtn = document.getElementById('exportBtn');
            const monthFilter = document.getElementById('monthFilter');
            
            function updateExportUrl() {
                const params = new URLSearchParams(window.location.search);
                const month = monthFilter.value;
                
                if (month) {
                    params.set('month', month);
                } else {
                    params.delete('month');
                }
                
                exportBtn.href = "{{ route('mitra.rekap.export') }}?" + params.toString();
            }
            
            // Update on page load
            updateExportUrl();
            
            // Update when month changes
            monthFilter.addEventListener('change', updateExportUrl);
        });
    </script>
</body>
