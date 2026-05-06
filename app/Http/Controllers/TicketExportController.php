<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan pakai package dompdf

class TicketExportController extends Controller
{
    public function export(Request $request)
    {
        $query = Ticket::with(['user', 'technician']);
        $allRooms = config('options.rooms') ?? [];

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

        // 2. Filter Unit
        if ($request->filled('unit') && isset($allRooms[$request->unit])) {
            $query->whereIn('location', $allRooms[$request->unit]);
        }

        // 3. Filter Ruangan Spesifik
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // 4. Filter Teknisi
        if ($request->filled('staff')) {
            $query->where('technician_id', $request->staff);
        }

        // 5. Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ambil data yang sudah terfilter
        $tickets = $query->latest()->get();

        // Generate PDF
        $pdf = Pdf::loadView('exports.tickets', [
            'tickets' => $tickets,
            'filters' => $request->all()
        ])->setPaper('a4', 'landscape');

        // Menggunakan stream() agar bisa di-preview di browser, ganti ke download() jika ingin langsung unduh
        return $pdf->stream('Laporan_Tiket_IT.pdf');
    }
}
