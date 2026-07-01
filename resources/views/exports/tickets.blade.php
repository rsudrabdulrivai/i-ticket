<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Tiket IT</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 15mm 12mm;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .header p {
            margin: 0;
            color: #4a5568;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #cefad0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        tr {
            page-break-inside: avoid;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #64748b;
            font-size: 9px;
            display: block;
            margin-top: 2px;
        }

        /* Style Kop Surat Resmi */
        .kop-surat {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 10px;
        }

        .kop-surat td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .logo-kiri {
            width: 12%;
            text-align: left;
        }

        .logo-kanan {
            width: 12%;
            text-align: right;
        }

        .text-kop {
            width: 76%;
            text-align: center;
            line-height: 1.2;
        }

        .text-kop .instansi-1 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .text-kop .instansi-2 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .text-kop .alamat {
            font-size: 9px;
            font-style: normal;
            color: #222;
        }

        .garis-kop {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        .info-filter {
            width: auto;
            min-width: 350px;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .info-filter td {
            border: none !important;
            padding: 3px 0;
        }
    </style>
</head>

<body>

    {{-- Kop Surat --}}
    <table class="kop-surat">
        <tr>
            <td class="logo-kiri">
                <img src="{{ public_path('berau.png') }}" style="height: 70px; width: auto;">
            </td>
            <td class="text-kop">
                <div class="instansi-1">Pemerintah Kabupaten Berau</div>
                <div class="instansi-2">Rumah Sakit Umum Daerah dr. Abdul Rivai</div>
                <div class="alamat">
                    Jalan Pulau Panjang No. 276 Kode Pos. 77311 Telp (0554) 21098 Fax. 21098<br>
                    Website: rsuddrabdulrivai.co.id / E-Mail: rsuddrabdulrivai@gmail.com<br>
                    <strong>TANJUNG REDEB</strong>
                </div>
            </td>
            <td class="logo-kanan">
                <img src="{{ public_path('logo.png') }}" style="height: 65px; width: auto;">
            </td>
        </tr>
    </table>

    <div class="garis-kop"></div>

    <div style="text-align: center; margin-bottom: 15px;">
        <h3 style="margin: 0; font-size: 16px; text-transform: uppercase;">Laporan Perbaikan IT</h3>
        <p style="margin: 2px 0 0 0; font-size: 12px; color: #4a5568;">RSUD dr. Abdul Rivai</p>
    </div>

    {{-- Bagian Profil Informasi Filter Atas --}}
    <div class="filter-info-box">
        <table class="info-filter">
            <tr>
                <td width="80">Nama</td>
                <td width="15">:</td>
                <td>
                    {{-- Nama user yang sedang login melakukan export selalu muncul --}}
                    {{ auth()->check() ? auth()->user()->name : 'Sistem / Administrator' }}
                </td>
            </tr>
            <tr>
                <td>Periode</td>
                <td>:</td>
                <td>
                    @if(isset($dateStart) && isset($dateEnd) && $dateStart && $dateEnd)
                        {{ \Carbon\Carbon::parse($dateStart)->format('d/m/Y') }} s.d {{ \Carbon\Carbon::parse($dateEnd)->format('d/m/Y') }}
                    @else
                        Semua Waktu
                    @endif
                </td>
            </tr>
            {{-- DINAMIS: Baris "Teknisi" hanya muncul di atas JIKA monitor sedang memfilter teknisi tertentu --}}
            @if($selectedTechnicianName !== 'Semua Teknisi')
            <tr>
                <td>Teknisi</td>
                <td>:</td>
                <td><strong>{{ $selectedTechnicianName }}</strong></td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Tabel Utama Data Tiket --}}
    <table>
        <thead>
            <tr>
                <th width="7%" class="text-center">ID</th>
                <th width="15%">Waktu</th>
                <th width="{{ $selectedTechnicianName === 'Semua Teknisi' ? '23%' : '30%' }}">Lokasi</th>
                <th width="{{ $selectedTechnicianName === 'Semua Teknisi' ? '25%' : '33%' }}">Kendala</th>
                
                {{-- KOLOM TABEL TEKNISI: Hanya muncul jika filter bernilai 'Semua Teknisi' --}}
                @if($selectedTechnicianName === 'Semua Teknisi')
                    <th width="15%">Teknisi</th>
                @endif
                
                <th width="15%" class="text-center">Status</th>
                <th width="10%" class="text-center">Prioritas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
            <tr>
                <td class="text-center">{{ $ticket->id }}</td>
                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    <strong>{{ $ticket->location }}</strong>
                </td>
                <td>
                    <strong>{{ $ticket->subject }}</strong>
                    <span class="text-muted">{{ $ticket->category }}</span>
                </td>
                
                {{-- ISI TABEL TEKNISI: Ikut bersembunyi secara otomatis jika di atas sudah ada nama teknisinya --}}
                @if($selectedTechnicianName === 'Semua Teknisi')
                    <td>{{ $ticket->technician->name ?? '-' }}</td>
                @endif
                
                <td class="text-center">{{ strtoupper($ticket->status) }}</td>
                <td class="text-center">{{ strtoupper($ticket->priority) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $selectedTechnicianName === 'Semua Teknisi' ? 7 : 6 }}" class="text-center">
                    Tidak ada data tiket pada filter ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>