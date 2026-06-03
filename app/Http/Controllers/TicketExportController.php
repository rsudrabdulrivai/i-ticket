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

        // Inisialisasi teks filter untuk di-render di PDF
        $selectedUnit = 'Semua Unit';
        $selectedLocation = 'Semua Ruangan';
        $selectedTechnicianName = 'Semua Teknisi';

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
            $selectedUnit = $request->unit; // Mengambil nama unit (ex: IGD, Rawat Jalan, dll)
        }

        // 3. Filter Ruangan Spesifik & Labeling
        if ($request->filled('location')) {
            $query->where('location', $request->location);
            $selectedLocation = $request->location; // Nama ruangan spesifik
        }

        // 4. Filter Teknisi & Labeling
        if ($request->filled('staff')) {
            $query->where('technician_id', $request->staff);
            $technician = User::find($request->staff);
            if ($technician) {
                $selectedTechnicianName = $technician->name;
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

        return $pdf->stream('Laporan_Tiket_IT.pdf');
    }
}