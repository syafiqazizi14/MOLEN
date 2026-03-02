<table>
    <thead>
        <tr>
            <th style="border: 1px solid #000000; text-align: center;">No</th>
            <th style="border: 1px solid #000000; text-align: left;">Nama</th>
            <th style="border: 1px solid #000000; text-align: center;">SOBAT ID</th>
            <th style="border: 1px solid #000000; text-align: center;">Kode Kecamatan</th>
            <th style="border: 1px solid #000000; text-align: left;">Nama Kecamatan</th>
            <th style="border: 1px solid #000000; text-align: left;">Tim</th>
            <th style="border: 1px solid #000000; text-align: left;">KRO</th>
            <th style="border: 1px solid #000000; text-align: left;">Jadwal Kegiatan (Awal &amp; Akhir)</th>
            <th style="border: 1px solid #000000; text-align: left;">Pemutakhiran</th>
            <th style="border: 1px solid #000000; text-align: left;">Sensus/Survei</th>
            <th style="border: 1px solid #000000; text-align: center;">Volume</th>
            <th style="border: 1px solid #000000; text-align: center;">Satuan</th>
            <th style="border: 1px solid #000000; text-align: right;">Nominal Honor</th>
            <th style="border: 1px solid #000000; text-align: right;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rekapMitra as $mitra)
            @php
                $surveyNames = array_keys($mitra['surveys_data'] ?? []);
                sort($surveyNames, SORT_NATURAL | SORT_FLAG_CASE);

                $timLines = [];
                $kroLines = [];
                $jadwalLines = [];
                $pemutakhiranLines = [];
                $surveiLines = [];
                $volumeLines = [];
                $satuanLines = [];
                $nominalLines = [];
                $totalLines = [];

                foreach ($surveyNames as $surveyName) {
                    $data = $mitra['surveys_data'][$surveyName] ?? null;
                    if (!$data) {
                        continue;
                    }

                    $detail = $surveyDetailMap[$surveyName] ?? ['kro' => '-', 'jadwal_kegiatan' => '-'];

                    $timLines[] = $surveyTeamMap[$surveyName] ?? '-';
                    $kroLines[] = $detail['kro'] ?? '-';
                    $jadwalLines[] = $detail['jadwal_kegiatan'] ?? '-';
                    $pemutakhiranLines[] = '-';
                    $surveiLines[] = $surveyName;
                    $volumeLines[] = $data['vol'];
                    $satuanLines[] = 'Dok';
                    $nominalLines[] = $data['honor_satuan'];
                    $totalLines[] = $data['total'];
                }

                if (empty($surveyNames)) {
                    $timLines = ['-'];
                    $kroLines = ['-'];
                    $jadwalLines = ['-'];
                    $pemutakhiranLines = ['-'];
                    $surveiLines = ['-'];
                    $volumeLines = ['-'];
                    $satuanLines = ['-'];
                    $nominalLines = ['-'];
                    $totalLines = ['-'];
                }

                $lineCount = max(
                    count($timLines),
                    count($kroLines),
                    count($jadwalLines),
                    count($pemutakhiranLines),
                    count($surveiLines),
                    count($volumeLines),
                    count($satuanLines),
                    count($nominalLines),
                    count($totalLines)
                );
            @endphp
            @for ($i = 0; $i < $lineCount; $i++)
                <tr>
                    @if ($i === 0)
                        <td rowspan="{{ $lineCount }}" style="border: 1px solid #000000; text-align: center; vertical-align: top;">
                            {{ $loop->iteration }}
                        </td>
                        <td rowspan="{{ $lineCount }}" style="border: 1px solid #000000; text-align: left; vertical-align: top;">
                            {{ $mitra['nama'] }}
                        </td>
                        <td rowspan="{{ $lineCount }}" style="border: 1px solid #000000; text-align: center; vertical-align: top;">
                            {{ $mitra['sobat_id'] }}
                        </td>
                        <td rowspan="{{ $lineCount }}" style="border: 1px solid #000000; text-align: center; vertical-align: top;">
                            {{ $mitra['kode_kec'] }}
                        </td>
                    @endif
                    <td style="border: 1px solid #000000; text-align: left;">{{ e($mitra['nama_kec'] ?? '-') }}</td>
                    <td style="border: 1px solid #000000; text-align: left;">{{ e($timLines[$i] ?? '-') }}</td>
                    <td style="border: 1px solid #000000; text-align: left;">{{ e($kroLines[$i] ?? '-') }}</td>
                    <td style="border: 1px solid #000000; text-align: left;">{{ e($jadwalLines[$i] ?? '-') }}</td>
                    <td style="border: 1px solid #000000; text-align: left;">{{ e($pemutakhiranLines[$i] ?? '-') }}</td>
                    <td style="border: 1px solid #000000; text-align: left;">{{ e($surveiLines[$i] ?? '-') }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $volumeLines[$i] ?? '-' }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $satuanLines[$i] ?? '-' }}</td>
                    <td style="border: 1px solid #000000; text-align: right;">{{ $nominalLines[$i] ?? '-' }}</td>
                    <td style="border: 1px solid #000000; text-align: right;">{{ $totalLines[$i] ?? '-' }}</td>
                </tr>
            @endfor
        @endforeach
    </tbody>
</table>
