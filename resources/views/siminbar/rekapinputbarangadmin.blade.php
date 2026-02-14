<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('viteall')
    <link rel="icon" href="/Logo BPS.png" type="image/png">
    <title>Rekap Tambah Stok</title>
</head>

<body class="h-full">
    <!-- SweetAlert Logic -->
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
    @if (Auth::user()->is_admin == 1 || Auth::user()->jabatan == 'Kasubag Umum')
        <!-- component -->
        <div x-data="{ sidebarOpen: false }" class="flex h-screen">
            <x-sidebar></x-sidebar>
            <div class="flex flex-col flex-1 overflow-hidden">
                <x-navbar></x-navbar>
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200">
                    <div class="container px-6 py-8 mx-auto">
                        <h3 class="text-3xl font-medium text-gray-700">Rekap Tambah Stok</h3>
                        <div class="mt-8">
                            <div class=" mb-3">
                                <div class="w-full md:w-1/2 flex flex-col md:flex-row">
                                    <div class="flex flex-grow mb-2 md:mb-0">
                                        <input type="text" id="searchInput"
                                            class="w-full border border-gray-300 rounded p-2"
                                            placeholder="Cari berdasarkan barang">
                                    </div>
                                </div>
                                <div class="w-full  flex flex-col md:flex-row mt-3">
                                    <div class="flex flex-grow mb-2 md:mb-0">
                                        <div id="date-range-picker" date-rangepicker class="flex items-center">
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                                    </svg>
                                                </div>
                                                <input id="datepicker-range-start" name="start" type="text"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Select date start">
                                            </div>
                                            <span class="mx-1 text-gray-500">to</span>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                                    </svg>
                                                </div>
                                                <input id="datepicker-range-end" name="end" type="text"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Select date end">
                                            </div>
                                        </div>


                                    </div>

                                </div>

                        <p id="searchResultMessage" class="mt-2 text-gray-600"></p>

                        <div class="w-full md:w-1/2 flex items-center space-x-2 mt-3">
                            <button id="submitButton"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                Submit
                            </button>
                            <a id="resetButton" href="{{ url()->current() }}"
                                class="px-4 py-2 bg-gray-300 text-black rounded-lg hover:bg-gray-400">
                                Reset
                            </a>
                            <!-- Export pakai FORM GET -->
                              <form id="exportForm" method="GET"
                                    action="{{ url('/rekap-input/export-pdf') }}"
                                    target="_blank" class="inline">
                                <input type="hidden" name="search" id="ex-search">
                                <input type="hidden" name="start_date" id="ex-start">
                                <input type="hidden" name="end_date" id="ex-end">
                                <button type="submit"
                                  class="bg-yellow-500 text-white rounded-md px-4 py-2">
                                  {{ __('Export PDF') }}
                                </button>
                              </form>
                        </div>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="flex flex-col mt-8">
                    <div class="py-2 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                        <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Tanggal</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Barang</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Tambah Stok</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Stok Sekarang</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">Edit</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @forelse ($items as $row)
                                        <tr id="{{ $row->id }}"
                                            data-jumlahtambah="{{ $row->jumlahtambah }}"
                                            data-stoktersedia="{{ $row->stoktersedia }}">
                                            <td class="px-6 py-4 border-b">
                                                {{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->translatedFormat('j F Y') }}
                                            </td>

                                            <td class="px-6 py-4 border-b">{{ $row->namabarang }}</td>

                                            {{-- Tambah Stok (inputbarangs->jumlahtambah) --}}
                                            <td class="px-6 py-4 border-b">
                                                <div class="tambah-wrap">
                                                    <span class="tambah-text">{{ number_format($row->jumlahtambah) }}</span>
                                                </div>
                                            </td>

                                            {{-- Stok Sekarang (barangs->stoktersedia) --}}
                                            <td class="px-6 py-4 border-b">
                                                <div class="stok-wrap">
                                                    <span class="stok-text">{{ number_format($row->stoktersedia) }}</span>
                                                </div>
                                            </td>

                                            {{-- Edit --}}
                                            <td class="px-6 py-4 border-b">
                                                <button class="edit-btn px-3 py-1 rounded bg-white border hover:bg-gray-50" title="Edit baris ini">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Data tidak ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-center mt-4">
                        @if(method_exists($items, 'links'))
  <div class="flex justify-center mt-4">
    {{ $items->links() }}
  </div>
@endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Modal Detail (opsional, kalau dipakai tombol .detail-btn) --}}
<div id="static-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
     class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full">
    <div class="fixed inset-0 bg-gray-800 opacity-50"></div>
    <div class="relative p-4 w-full max-w-2xl max-h-full bg-white rounded-lg shadow">
        <div class="flex items-center justify-between p-4 border-b rounded-t">
            <h3 class="text-xl font-semibold text-gray-900">Detail Stok Masuk</h3>
            <button type="button" class="text-gray-400 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center"
                    data-modal-hide="static-modal">
                <svg class="w-3 h-3" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L13 13M13 1L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
            <div><b>Tanggal:</b> <span id="m-tanggal"></span></div>
            <div><b>Barang:</b> <span id="m-barang"></span></div>
            <div><b>Tambah Stok:</b> <span id="m-tambah"></span></div>
            <div><b>Total Stok:</b> <span id="m-total"></span></div>
            <p id="m-id" class="hidden"></p>

            <div class="flex items-center pt-4 border-t">
                <button data-modal-hide="static-modal" type="button"
                        class="px-4 py-2 rounded border bg-white hover:bg-gray-50">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@else
    <script>
        alert("Anda tidak memiliki akses ke halaman ini");
        window.history.back();
    </script>
@endif

{{-- JS --}}
<script>
/* ---- util export pdf (jika tombol dipakai) ---- */
// Sinkronisasi nilai filter saat form export disubmit
document.getElementById('exportForm').addEventListener('submit', function () {
  document.getElementById('ex-search').value =
    document.getElementById('searchInput').value.trim();

  document.getElementById('ex-start').value =
    document.getElementById('datepicker-range-start').value;

  document.getElementById('ex-end').value =
    document.getElementById('datepicker-range-end').value;
});


/* ---- Inline edit: Tambah Stok & Stok Sekarang ---- */
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function formatID(x){ return new Intl.NumberFormat('id-ID').format(Number(x||0)); }
function toInput(num, cls, min=0){
  const v = Number(num || 0);
  return `<input type="number" min="${min}" step="1"
           class="w-28 md:w-32 border border-gray-300 rounded px-2 py-1 ${cls}"
           value="${v}">`;
}

function enterEdit(row){
  if(row.dataset.editing==='1') return;
  row.dataset.editing='1';
  row.querySelector('.tambah-wrap').innerHTML = toInput(row.dataset.jumlahtambah,'input-tambah');
  row.querySelector('.stok-wrap').innerHTML   = toInput(row.dataset.stoktersedia,'input-stok');

  const editCell = row.querySelector('td:last-child');
  editCell.innerHTML = `
    <div class="flex items-center gap-2">
      <button class="save-btn px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700" title="Simpan"><i class="fas fa-check"></i></button>
      <button class="cancel-btn px-3 py-1 rounded bg-gray-300 hover:bg-gray-400" title="Batal"><i class="fas fa-times"></i></button>
    </div>`;
}

function exitEdit(row,{update=false,tambah=0,stok=0}={}){
  if(update){
    row.dataset.jumlahtambah=tambah;
    row.dataset.stoktersedia=stok;
    row.querySelector('.tambah-wrap').innerHTML=`<span class="tambah-text">${formatID(tambah)}</span>`;
    row.querySelector('.stok-wrap').innerHTML=`<span class="stok-text">${formatID(stok)}</span>`;
  }else{
    row.querySelector('.tambah-wrap').innerHTML=`<span class="tambah-text">${formatID(row.dataset.jumlahtambah)}</span>`;
    row.querySelector('.stok-wrap').innerHTML=`<span class="stok-text">${formatID(row.dataset.stoktersedia)}</span>`;
  }
  const editCell = row.querySelector('td:last-child');
  editCell.innerHTML = `<button class="edit-btn px-3 py-1 rounded bg-white border hover:bg-gray-50" title="Edit baris ini"><i class="fas fa-pen"></i></button>`;
  delete row.dataset.editing;
}

document.addEventListener('click', async (e)=>{
  const editBtn=e.target.closest('.edit-btn');
  const saveBtn=e.target.closest('.save-btn');
  const cancelBtn=e.target.closest('.cancel-btn');

  if(editBtn){ enterEdit(editBtn.closest('tr')); return; }
  if(cancelBtn){ exitEdit(cancelBtn.closest('tr'),{update:false}); return; }

  if(saveBtn){
    const row=saveBtn.closest('tr');
    const id=row.id;
    const tambah=Number(row.querySelector('.input-tambah').value||0);
    const stok=Number(row.querySelector('.input-stok').value||0);

    if(tambah<0||stok<0){ alert('Nilai tidak boleh negatif.'); return; }

    try{
      const res=await fetch(`/inputbarangs/${id}`,{
        method:'PUT',
        headers:{
          'Content-Type':'application/json',
          'Accept':'application/json',
          'X-CSRF-TOKEN':csrf,
        },
        body:JSON.stringify({jumlahtambah:tambah, stoktersedia:stok})
      });
      if(!res.ok){
        let msg='Gagal menyimpan perubahan.';
        try{ const j=await res.json(); if(j?.message) msg=j.message; }catch{}
        throw new Error(msg);
      }
      exitEdit(row,{update:true,tambah,stok});
    }catch(err){
      console.error(err);
      alert(err.message||'Terjadi kesalahan jaringan.');
    }
  }
});

/* ---- Prefill filter dari query string ---- */
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('search');
    const startDate = urlParams.get('start_date');
    const endDate = urlParams.get('end_date');

    if (searchQuery) document.getElementById('searchInput').value = searchQuery;
    if (startDate) document.getElementById('datepicker-range-start').value = startDate;
    if (endDate) document.getElementById('datepicker-range-end').value = endDate;

    let message = '';
    if (searchQuery) message += `Hasil pencarian untuk: "${searchQuery}"`;
    if (startDate && endDate) message += (message ? ' | ' : '') + `rentang tanggal: ${startDate} hingga ${endDate}`;
    document.getElementById('searchResultMessage').textContent = message;
});

/* ---- Submit filter ---- */
document.getElementById('submitButton').addEventListener('click', function () {
    const s  = document.getElementById('searchInput').value.trim();
    const sd = document.getElementById('datepicker-range-start').value;
    const ed = document.getElementById('datepicker-range-end').value;

    const url = new URL(window.location.href);
    s ? url.searchParams.set('search', s) : url.searchParams.delete('search');
    sd ? url.searchParams.set('start_date', sd) : url.searchParams.delete('start_date');
    ed ? url.searchParams.set('end_date', ed) : url.searchParams.delete('end_date');
    window.location.href = url.toString();
});

['searchInput', 'datepicker-range-start', 'datepicker-range-end'].forEach(id => {
    document.getElementById(id).addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('submitButton').click();
        }
    });
});

/* ---- Detail Modal (kalau dipakai) ---- */
document.querySelectorAll('.detail-btn').forEach(button => {
    button.addEventListener('click', function() {
        const rowId = this.closest('tr').id;
        const modal = document.getElementById('static-modal');
        modal.classList.remove('hidden');

        fetch(`/inputbarangs/${rowId}`)
            .then(res => { if (!res.ok) throw new Error('Data tidak ditemukan'); return res.json(); })
            .then(d => {
                document.getElementById('m-id').textContent = d.id;
                document.getElementById('m-tanggal').textContent = d.tanggal;
                document.getElementById('m-barang').textContent = d.namabarang;
                document.getElementById('m-tambah').textContent = d.jumlahtambah;
                document.getElementById('m-total').textContent = d.stoktersedia;
            })
            .catch(err => {
                console.error(err);
                alert('Gagal memuat detail.');
            });

        modal.querySelectorAll('[data-modal-hide]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                modal.classList.add('hidden');
            });
        });
    });
});
</script>
</body>
</html>
