<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketExportController extends Controller
{
    public function export(Request $request)
    {
        $query = Ticket::with(['user', 'technician']);
        $allRooms = config('options.rooms') ?? [];

<<<<<<< HEAD
        // Inisialisasi teks filter untuk di-render di PDF sesuai pilihan di monitor
        $selectedUnit = 'Semua Unit';
        $selectedLocation = 'Semua Ruangan';
        $selectedTechnicianName = 'Semua Teknisi'; // Default jika filter teknisi dikosongkan
=======
        // Inisialisasi teks filter untuk di-render di PDF
        $selectedUnit = 'Semua Unit';
        $selectedLocation = 'Semua Ruangan';
        $selectedTechnicianName = 'Semua Teknisi';
>>>>>>> 5c372590fe0c2debcdfef2db7048eb464f64054f

        // 1. Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                    ->orWhere('subject', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // 2. Filter Unit & Labeling
        if ($request->filled('unit') && isset($allRooms[$request->unit])) {
            $query->whereIn('location', $allRooms[$request->unit]);
<<<<<<< HEAD
            $selectedUnit = $request->unit;
=======
            $selectedUnit = $request->unit; // Mengambil nama unit (ex: IGD, Rawat Jalan, dll)
>>>>>>> 5c372590fe0c2debcdfef2db7048eb464f64054f
        }

        // 3. Filter Ruangan Spesifik & Labeling
        if ($request->filled('location')) {
            $query->where('location', $request->location);
<<<<<<< HEAD
            $selectedLocation = $request->location;
        }

        // 4. KEMBALI KE ASLI: Filter Teknisi Murni Berdasarkan Pilihan Filter di Monitor
=======
            $selectedLocation = $request->location; // Nama ruangan spesifik
        }

        // 4. Filter Teknisi & Labeling
>>>>>>> 5c372590fe0c2debcdfef2db7048eb464f64054f
        if ($request->filled('staff')) {
            $query->where('technician_id', $request->staff);
            $technician = User::find($request->staff);
            if ($technician) {
<<<<<<< HEAD
                $selectedTechnicianName = $technician->name; // Nama teknisi sesuai yang dipilih di filter
=======
                $selectedTechnicianName = $technician->name;
>>>>>>> 5c372590fe0c2debcdfef2db7048eb464f64054f
            }
        }

        // 5. Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 6. Filter Rentang Tanggal
        if ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereBetween('created_at', [
                $request->date_start . ' 00:00:00', 
                $request->date_end . ' 23:59:59'
            ]);
        }

        // Ambil data terfilter
        $tickets = $query->oldest()->get();

        // Generate PDF dengan membawa semua informasi filter lengkap
        $pdf = Pdf::loadView('exports.tickets', [
            'tickets'                => $tickets,
            'dateStart'              => $request->date_start,
            'dateEnd'                => $request->date_end,
            'selectedUnit'           => $selectedUnit,
            'selectedLocation'       => $selectedLocation,
            'selectedTechnicianName' => $selectedTechnicianName,
        ])->setPaper('a4', 'portrait');

<<<<<<< HEAD
        // Nama file unduhan dibuat berdasarkan filter teknisi yang dipilih
        $filename = 'Laporan_Tiket_IT_' . str_replace(' ', '_', $selectedTechnicianName) . '.pdf';

        return $pdf->stream($filename);
=======
        return $pdf->stream('Laporan_Tiket_IT.pdf');
>>>>>>> 5c372590fe0c2debcdfef2db7048eb464f64054f
    }
}