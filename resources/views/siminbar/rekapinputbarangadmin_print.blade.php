<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Rekap Tambah Stok — Cetak</title>
  <link rel="icon" href="/Logo BPS.png" type="image/png">
  <style>
    @page { size: A4 portrait; margin: 12mm; }
    html,body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Inter,Arial,"Noto Sans",sans-serif;color:#111827;font-size:12px;}
    h2{margin:0 0 6px 0;}
    .muted{color:#6b7280;}
    .doc-head{display:flex;align-items:center;gap:12px;margin-bottom:10px;}
    .doc-head img{width:36px;height:36px;object-fit:contain;}
    .filters{margin:6px 0 12px;font-size:12px;}
    table{width:100%;border-collapse:collapse;table-layout:fixed;}
    thead{display:table-header-group;}
    th,td{border:1px solid #e5e7eb;padding:6px 8px;vertical-align:top;overflow-wrap:anywhere;}
    th{background:#f9fafb;text-transform:uppercase;font-weight:600;font-size:11px;color:#6b7280;}
    tr{page-break-inside:avoid;}
    .right{text-align:right;}
    .center{text-align:center;}
    .noprint{margin-top:12px;}
    @media print{.noprint{display:none!important;}}
  </style>
</head>
<body>
  <div class="doc-head">
    <img src="/Logo BPS.png" alt="Logo">
    <div>
      <h2>Rekap Tambah Stok</h2>
      <div class="muted">  Tgl cetak:  {{ \Illuminate\Support\Carbon::now()->locale('id')->translatedFormat('j F Y H:i') }}
        </div>

    </div>
  </div>

  <div class="filters">
    @if(!empty($search))
      <div><b>Kata kunci:</b> {{ $search }}</div>
    @endif
    @if(!empty($startDate) || !empty($endDate))
        <div>
          <b>Rentang tanggal:</b>
          {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d-m-Y') : '—' }}
          s/d
          {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d-m-Y') : '—' }}
        </div>
    @endif
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:16%">Tanggal</th>
        <th style="width:34%">Barang</th>
        <th style="width:16%" class="right">Tambah Stok</th>
        <th style="width:18%" class="right">Stok Sekarang</th>
      </tr>
    </thead>
    <tbody>
    @forelse($items as $row)
  <tr>
    <td>{{ \Illuminate\Support\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
    <td>{{ $row->namabarang }}</td>
    <td class="right">{{ number_format((float)$row->jumlahtambah, 0, ',', '.') }}</td>
    <td class="right">{{ number_format((float)$row->stoktersedia, 0, ',', '.') }}</td>
  </tr>
@empty
  <tr><td colspan="4" class="center muted">Tidak ada data untuk filter ini.</td></tr>
@endforelse
    </tbody>
    <tfoot>
      <tr><td colspan="4" class="right muted">Total baris: {{ $items->count() }}</td></tr>
    </tfoot>
  </table>

  <div class="noprint">
    <button onclick="window.print()">Cetak / Simpan PDF</button>
  </div>

  <script>window.addEventListener('load',()=>setTimeout(()=>window.print(),300));</script>
</body>
</html>
