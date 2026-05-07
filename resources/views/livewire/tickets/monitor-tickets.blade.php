<?php

use Livewire\Volt\Component;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component {
    public $search = '';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $staffFilter = '';
    public $locationFilter = '';
    public $selectedTicketId;
    public $tindak_lanjut = '';
    public $keterangan_it = '';
    public $kategori_perubahan = '';
    public $kategori_alat = '';
    public $detailTicket;
    public $unitFilter = '';
    public $listPerubahan = ['Perbaikan Ringan', 'Penggantian Komponen', 'Update Konfigurasi', 'Edukasi User', 'Lain-lain'];
    public $listAlat = ['Komputer/PC', 'Printer/Scanner', 'Jaringan/Internet', 'Aplikasi/SIMRS', 'Lain-lain'];

    public function with(): array
    {
        // 1. Inisialisasi Query
        $query = Ticket::with(['user', 'technician']);

        // 2. Ambil data dari config/options.php
        $allRooms = config('options.rooms') ?? [];

        // 3. LOGIC PENCARIAN
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                    ->orWhere('subject', 'like', '%' . $this->search . '%')
                    ->orWhere('location', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($u) {
                        $u->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->unitFilter && isset($allRooms[$this->unitFilter])) {
            $roomsInUnit = $allRooms[$this->unitFilter];
            $query->whereIn('location', $roomsInUnit);
        }

        if ($this->locationFilter) {
            $query->where('location', $this->locationFilter);
        }

        if ($this->staffFilter) {
            $query->where('technician_id', $this->staffFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $rooms = ($this->unitFilter && isset($allRooms[$this->unitFilter]))
            ? $allRooms[$this->unitFilter]
            : [];

        return [
            'all_tickets' => $query->latest()->get(),
            'it_staffs'   => User::where('is_it_staff', true)->get(),
            // PERBAIKAN: Ubah 'categories' menjadi 'units' agar sesuai dengan @foreach di HTML
            'units'       => array_keys($allRooms),
            'rooms'       => $rooms,
        ];
    }

    public function updatedUnitFilter()
    {
        $this->reset('locationFilter');
    }

    public function updateStatus($id, $status)
    {
        $ticket = Ticket::find($id);
        $data = ['status' => $status];

        if ($status === 'On Progress') {
            $data['technician_id'] = Auth::id();
            $data['taken_at'] = now();
        }

        $ticket->update($data);
        session()->flash('monitor_msg', 'Tiket #' . $id . ' sekarang berstatus ' . $status);
    }

    public function openClosingModal($id)
    {
        $this->selectedTicketId = $id;
        $this->reset(['tindak_lanjut', 'keterangan_it', 'kategori_perubahan', 'kategori_alat']);
        $this->modal('closing-modal')->show();
    }

    // FUNGSI BARU: Untuk menampilkan detail tiket yang sudah Closed
    public function showDetail($id)
    {
        $this->detailTicket = Ticket::with(['user', 'technician'])->find($id);
        $this->modal('detail-modal')->show();
    }

    public function exportPdf()
    {
        // Kumpulkan semua filter yang sedang aktif
        $params = [
            'search'   => $this->search,
            'unit'     => $this->unitFilter,
            'location' => $this->locationFilter,
            'staff'    => $this->staffFilter,
            'status'   => $this->statusFilter,
        ];

        // Redirect ke route export dengan membawa parameter filter
        return redirect()->route('tickets.export-pdf', $params);
    }

    public function saveClosing()
    {
        $this->validate([
            'kategori_alat' => 'required',
            'kategori_perubahan' => 'required',
            'tindak_lanjut' => 'required|min:10',
        ], [

            'kategori_alat.required' => 'Pilih alat yang diperbaiki.',
            'kategori_perubahan.required' => 'Pilih kategori perubahan.',
            'tindak_lanjut.required' => 'Tindak lanjut wajib diisi agar terdokumentasi.',
        ]);

        $ticket = Ticket::find($this->selectedTicketId);

        $ticket->update([
            'status' => 'Closed',
            'tindak_lanjut' => $this->tindak_lanjut,
            'keterangan_it' => $this->keterangan_it,
            'kategori_perubahan' => $this->kategori_perubahan,
            'kategori_alat' => $this->kategori_alat,
            'closed_at' => now(),
        ]);

        // Tutup modal setelah berhasil
        $this->modal('closing-modal')->close();

        session()->flash('message', 'Tiket berhasil diselesaikan dan ditutup.');
    }
}; ?>

<div class="space-y-6">
    @if (session()->has('monitor_msg'))
    <div class="p-3 bg-blue-600 text-white rounded-lg text-sm font-bold animate-pulse">
        {{ session('monitor_msg') }}
    </div>
    @endif
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-4">
        {{-- Row Atas: Search & Status --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="w-full md:w-1/2">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Cari ID, Judul, Pelapor, atau Lokasi..." />
            </div>

            <div class="flex p-1 bg-slate-100 rounded-lg border border-slate-200 w-full md:w-auto overflow-x-auto">
                <button wire:click="$set('statusFilter', '')" class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-widest transition {{ $statusFilter === '' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500' }}">Semua</button>
                <button wire:click="$set('statusFilter', 'Open')" class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-widest transition {{ $statusFilter === 'Open' ? 'bg-white shadow-sm text-red-600' : 'text-gray-500' }}">Open</button>
                <button wire:click="$set('statusFilter', 'On Progress')" class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-widest transition {{ $statusFilter === 'On Progress' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500' }}">Proses</button>
                <button wire:click="$set('statusFilter', 'Closed')" class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-widest transition {{ $statusFilter === 'Closed' ? 'bg-white shadow-sm text-green-600' : 'text-gray-500' }}">Selesai</button>
            </div>
        </div>

        {{-- Baris 2: Filter Dinamis --}}
        <div class="flex flex-col md:flex-row gap-3 pt-4 border-t border-gray-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:flex gap-3 flex-grow">

                {{-- Filter Bidang/Unit --}}
                <div class="w-full md:w-44">
                    <flux:select wire:model.live="unitFilter" icon="building-office" class="bg-slate-50/50">
                        <flux:select.option value="">Semua Unit</flux:select.option>

                        @foreach($units as $unit)
                        <flux:select.option value="{{ $unit }}">{{ $unit }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                {{-- Filter Ruangan (Dinamis berdasarkan Unit) --}}
                <div class="w-full md:w-56">
                    <flux:select
                        wire:model.live="locationFilter"
                        icon="map-pin"
                        class="bg-slate-50/50"
                        :disabled="empty($rooms)"
                        wire:key="location-select-{{ $unitFilter }}">

                        <flux:select.option value="">
                            {{ $unitFilter ? 'Semua Ruangan' : 'Pilih Unit Dulu' }}
                        </flux:select.option>

                        @foreach($rooms as $room)
                        <flux:select.option value="{{ $room }}">{{ $room }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                {{-- Filter Teknisi (Tetap sama) --}}
                <div class="w-full md:w-48">
                    <flux:select wire:model.live="staffFilter" icon="user" class="bg-slate-50/50">
                        <flux:select.option value="">Semua Teknisi</flux:select.option>

                        @foreach($it_staffs as $staff)
                        <flux:select.option value="{{ $staff->id }}">{{ $staff->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <flux:button wire:click="exportPdf" icon="printer" variant="outline" class="font-bold text-xs uppercase tracking-tight shadow-sm border-slate-200">
                Export PDF
            </flux:button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-4">Info Tiket</th>
                        <th class="px-6 py-4">Pelapor & Lokasi</th>
                        <th class="px-6 py-4">Kategori & Prioritas</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Teknisi</th>
                        <th class="px-6 py-4 text-center">Aksi IT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($all_tickets as $ticket)
                    <tr class="bg-white hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">#{{ $ticket->id }} - {{ $ticket->subject }}</div>
                            <div class="text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-900 font-medium">{{ $ticket->user->name }}</div>
                            <div class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider">{{ $ticket->location }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="block text-xs text-gray-500 mb-1">{{ $ticket->category }}</span>
                            @if($ticket->priority == 'Urgent')
                            <span class="px-2 py-0.5 bg-red-600 text-white text-[10px] rounded-full font-black animate-bounce inline-block">URGENT</span>
                            @else
                            <span class="text-xs font-bold text-gray-700">{{ $ticket->priority }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($ticket->status == 'Open')
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-[10px] font-bold border border-red-200">OPEN</span>
                            @elseif($ticket->status == 'On Progress')
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-[10px] font-bold border border-blue-200">ON PROGRESS</span>
                            @else
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold border border-green-200">CLOSED</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($ticket->technician_id)
                            <div class="flex items-center gap-2">
                                <div class="size-7 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-[10px] font-bold border border-indigo-200">
                                    {{ strtoupper(substr($ticket->technician->name, 0, 2)) }}
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ $ticket->technician->name }}</span>
                            </div>
                            @else
                            <span class="text-xs text-gray-400 italic font-light tracking-tight">Menunggu Teknisi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                @if($ticket->status == 'Open')
                                <flux:button wire:click="updateStatus({{ $ticket->id }}, 'On Progress')" variant="primary" size="sm">
                                    Ambil Tugas
                                </flux:button>
                                @elseif($ticket->status == 'On Progress')
                                <flux:button wire:click="openClosingModal({{ $ticket->id }})" variant="primary" size="sm" class="bg-green-600 hover:bg-green-700 border-none">
                                    Selesaikan
                                </flux:button>
                                @elseif($ticket->status == 'Closed')
                                <flux:button wire:click="showDetail({{ $ticket->id }})" variant="ghost" size="sm">
                                    Lihat Detail
                                </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center text-gray-400 italic text-lg">
                            Belum ada tiket masuk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Letakkan di bagian bawah, di luar table --}}
        <x-ticket-detail-modal :ticket="$detailTicket" />
    </div>