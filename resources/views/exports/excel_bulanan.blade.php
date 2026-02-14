<table>
    <thead>
        {{-- BARIS 1: NAMA TIM --}}
        <tr>
            <th rowspan="6"
                style="vertical-align: middle; font-weight: bold; border: 1px solid #000000; text-align: center;">No</th>
            <th rowspan="6" style="vertical-align: middle; font-weight: bold; border: 1px solid #000000; width: 30px;">
                Nama Lengkap</th>
            <th rowspan="6"
                style="vertical-align: middle; font-weight: bold; border: 1px solid #000000; text-align: center;">SOBAT
                ID</th>
            <th rowspan="6"
                style="vertical-align: middle; font-weight: bold; border: 1px solid #000000; text-align: center;">Kode
                Kec</th>

            @foreach ($uniqueSurveys as $surveyName)
                {{-- [BARU] Tampilkan Nama Tim berdasarkan Peta Survei --}}
                <th colspan="4"
                    style="text-align: left; font-weight: bold; background-color: #e2efda; border: 1px solid #000000;">
                    Tim: {{ $surveyTeamMap[$surveyName] ?? '-' }}
                </th>
            @endforeach

            {{-- [BARU] Header Total Bulanan (Paling Kanan) --}}
            <th rowspan="6"
                style="vertical-align: middle; font-weight: bold; border: 1px solid #000000; text-align: center; width: 150px;">
                TOTAL BULANAN</th>
        </tr>

        {{-- BARIS 2: KRO --}}
        <tr>
            @foreach ($uniqueSurveys as $surveyName)
                <th colspan="4" style="border: 1px solid #000000; text-align: left;">
                    KRO: {{ $surveyDetailMap[$surveyName]['kro'] ?? '-' }}
                </th>
            @endforeach
        </tr>

        {{-- BARIS 3: Jadwal --}}
        <tr>
            @foreach ($uniqueSurveys as $surveyName)
                <th colspan="4" style="border: 1px solid #000000; text-align: left;">
                    Jadwal Keg: {{ $surveyDetailMap[$surveyName]['jadwal_kegiatan'] ?? '-' }} s/d {{ $surveyDetailMap[$surveyName]['jadwal_berakhir'] ?? '-' }}
                </th>
            @endforeach
        </tr>

        {{-- BARIS 4: Pemutakhiran --}}
        <tr>
            @foreach ($uniqueSurveys as $surveyName)
                <th colspan="4" style="border: 1px solid #000000; text-align: left;">
                    Pemutakhiran: -
                </th>
            @endforeach
        </tr>

        {{-- BARIS 5: NAMA SURVEI --}}
        <tr>
            @foreach ($uniqueSurveys as $surveyName)
                <th colspan="4"
                    style="text-align: center; font-weight: bold; background-color: #fce4d6; border: 1px solid #000000;">
                    Sensus / Survei: {{ $surveyName }}
                </th>
            @endforeach
        </tr>

        {{-- BARIS 6: DETAIL KOLOM --}}
        <tr>
            @foreach ($uniqueSurveys as $surveyName)
                <th style="border: 1px solid #000000; text-align: center;">Vol</th>
                <th style="border: 1px solid #000000; text-align: center;">Satuan</th>
                <th style="border: 1px solid #000000; text-align: center;">Rate</th>
                <th style="border: 1px solid #000000; text-align: center;">Total</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($rekapMitra as $mitra)
            <tr>
                <td style="border: 1px solid #000000; text-align: center;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #000000;">{{ $mitra['nama'] }}</td>
                <td style="border: 1px solid #000000; text-align: center; mso-number-format:'\@';">
                    {{ $mitra['sobat_id'] }}</td>
                {{-- [BARU] Tampilkan Kode Kec --}}
                <td style="border: 1px solid #000000; text-align: center;">{{ $mitra['kode_kec'] }}</td>

                @foreach ($uniqueSurveys as $surveyName)
                    @php
                        $data = $mitra['surveys_data'][$surveyName] ?? null;
                    @endphp

                    @if ($data)
                        <td style="border: 1px solid #000000; text-align: center;">{{ $data['vol'] }}</td>
                        <td style="border: 1px solid #000000; text-align: center;">dok</td>
                        <td style="border: 1px solid #000000; text-align: right;">{{ $data['honor_satuan'] }}</td>
                        <td style="border: 1px solid #000000; text-align: right;">{{ $data['total'] }}</td>
                    @else
                        {{-- Kolom Kosong jika mitra tidak mengerjakan survei ini --}}
                        <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                        <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                        <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                        <td style="border: 1px solid #000000; background-color: #f2f2f2; text-align: right;">0</td>
                    @endif
                @endforeach

                {{-- [BARU] Kolom Grand Total Bulanan --}}
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right;">
                    {{ $mitra['grand_total'] }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
