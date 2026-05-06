<!DOCTYPE html>
<html>
<head>
    <title>Laporan Tiket IT</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .status-badge { padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN TIKET IT (iTicket)</h2>
        <p>Tanggal Cetak: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="15%">Waktu</th>
                <th width="20%">Pelapor & Lokasi</th>
                <th width="25%">Kendala</th>
                <th width="15%">Teknisi</th>
                <th width="10%">Status</th>
                <th width="10%">Prioritas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
            <tr>
                <td class="text-center">#{{ $ticket->id }}</td>
                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    <strong>{{ $ticket->user->name }}</strong><br>
                    <small>{{ $ticket->location }}</small>
                </td>
                <td>
                    <strong>{{ $ticket->subject }}</strong><br>
                    <small>{{ $ticket->category }}</small>
                </td>
                <td>{{ $ticket->technician->name ?? '-' }}</td>
                <td class="text-center">{{ strtoupper($ticket->status) }}</td>
                <td class="text-center">{{ strtoupper($ticket->priority) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data tiket pada filter ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>