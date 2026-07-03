<?php

use Livewire\Volt\Component;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

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
    public $priority = 'Medium';
    public $catatan_batal = '';
    public $isEditMode = false;
    public $editRooms = [];
    public $editUnit = '';
    public $editLocation = '';
    public $dateStart = '';
    public $dateEnd = '';

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

        if ($this->dateStart) {
            $query->whereDate('created_at', '>=', $this->dateStart);
        }

        if ($this->dateEnd) {
            $query->whereDate('created_at', '<=', $this->dateEnd);
        }

        $rooms = ($this->unitFilter && isset($allRooms[$this->unitFilter]))
            ? $allRooms[$this->unitFilter]
            : [];

        return [
            // Ubah ->get() menjadi ->paginate(10)
            'all_tickets' => $query->latest()->paginate(10),
            'it_staffs'   => User::where('is_it_staff', true)->get(),
            'units'       => array_keys($allRooms),
            'rooms'       => $rooms,
        ];
    }
    public function updatingDateStart()
    {
        $this->resetPage();
    }
    public function updatingDateEnd()
    {
        $this->resetPage();
    }
    // Fungsi otomatis berjalan saat variabel $search atau filter diubah
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    public function updatingUnitFilter()
    {
        $this->resetPage();
    }
    public function updatingLocationFilter()
    {
        $this->resetPage();
    }
    public function updatingStaffFilter()
    {
        $this->resetPage();
    }

    public function confirmTakeTicket()
    {
        $this->validate(['priority' => 'required']);

        $ticket = Ticket::find($this->selectedTicketId);
        $ticket->update([
            'status' => 'On Progress',
            'priority' => $this->priority, // Simpan prioritas
            'technician_id' => Auth::id(),
            'taken_at' => now(),
        ]);

        $this->modal('take-ticket-modal')->close();
        session()->flash('monitor_msg', 'Tiket #' . $this->selectedTicketId . ' berhasil diambil.');
    }

    // Tambahkan fungsi ini di dalam class
    public function openTakeModal($id)
    {
        $this->selectedTicketId = $id;
        $this->modal('take-ticket-modal')->show();
    }

    public function openEditModal($id)
    {
        $this->isEditMode = true;
        $this->selectedTicketId = $id;

        $ticket = Ticket::find($id);
        if (!$ticket) return;

        // Ambil isi lama ke dalam state form modal
        $this->tindak_lanjut = $ticket->tindak_lanjut;
        $this->keterangan_it = $ticket->keterangan_it;
        $this->kategori_perubahan = $ticket->kategori_perubahan;
        $this->kategori_alat = $ticket->kategori_alat;
        $this->editLocation = $ticket->location; // Gunakan variabel khusus edit

        // Deteksi unit asal berdasarkan ruangan lama agar select option ruangan muncul
        $allRooms = config('options.rooms') ?? [];
        foreach ($allRooms as $unitName => $roomsArray) {
            if (in_array($ticket->location, $roomsArray)) {
                $this->editUnit = $unitName; // Gunakan variabel khusus edit
                $this->editRooms = $roomsArray;
                break;
            }
        }

        $this->modal('closing-modal')->show();
    }

    // Listener otomatis ketika Unit diubah di dalam mode edit laporan
    public function updatedUnitFilter($value)
    {
        $allRooms = config('options.rooms') ?? [];
        $this->editRooms = $allRooms[$value] ?? [];
        $this->reset('locationFilter');
    }

    public function updatedEditUnit($value)
    {
        $allRooms = config('options.rooms') ?? [];
        $this->editRooms = $allRooms[$value] ?? [];
        $this->editLocation = '';
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
        $ticket = Ticket::find($id);
<<<<<<< HEAD
        if (!$ticket || (int) $ticket->technician_id != Auth::id()) {
=======
        if (!$ticket || (int)$ticket->technician_id != Auth::id()) {
>>>>>>> 5c372590fe0c2debcdfef2db7048eb464f64054f
            session()->flash('monitor_msg', 'Anda tidak berhak menyelesaikan tiket ini.');
            return;
        }

        $this->isEditMode = false;
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
        $params = [
            'search'   => $this->search,
            'unit'     => $this->unitFilter,
            'location' => $this->locationFilter,
            'staff'    => $this->staffFilter,
            'status'   => $this->statusFilter,
            'date_start' => $this->dateStart,
            'date_end'   => $this->dateEnd,
        ];

        $url = route('tickets.export-pdf', $params);
        $this->js("window.open('{$url}', '_blank')");
    }

    public function saveClosing()
    {
        $rules = [
            'kategori_alat' => 'required',
            'kategori_perubahan' => 'required',
            'tindak_lanjut' => 'required|min:10',
        ];

        if ($this->isEditMode) {
            $rules['editLocation'] = 'required'; // Validasi variabel baru
        }

        $this->validate($rules, [
            'kategori_alat.required' => 'Pilih alat yang diperbaiki.',
            'kategori_perubahan.required' => 'Pilih kategori perubahan.',
            'tindak_lanjut.required' => 'Tindak lanjut wajib diisi agar terdokumentasi.',
            'editLocation.required' => 'Ruangan penyesuaian wajib dipilih.',
        ]);

        $ticket = Ticket::find($this->selectedTicketId);

        if ($this->isEditMode) {
            $ticket->update([
                'tindak_lanjut' => $this->tindak_lanjut,
                'keterangan_it' => $this->keterangan_it,
                'kategori_perubahan' => $this->kategori_perubahan,
                'kategori_alat' => $this->kategori_alat,
                'location' => $this->editLocation, // Update lokasi berdasarkan input modal khusus
            ]);
            session()->flash('monitor_msg', 'Laporan tiket #' . $this->selectedTicketId . ' berhasil diperbarui.');
        } else {
<<<<<<< HEAD
            if (!$ticket || (int) $ticket->technician_id != Auth::id()) {
=======
            if (!$ticket || (int)$ticket->technician_id != Auth::id()) {
>>>>>>> 5c372590fe0c2debcdfef2db7048eb464f64054f
                $this->modal('closing-modal')->close();
                session()->flash('monitor_msg', 'Gagal memproses. Anda bukan teknisi yang ditugaskan.');
                return;
            }

            $ticket->update([
                'status' => 'Closed',
                'tindak_lanjut' => $this->tindak_lanjut,
                'keterangan_it' => $this->keterangan_it,
                'kategori_perubahan' => $this->kategori_perubahan,
                'kategori_alat' => $this->kategori_alat,
                'closed_at' => now(),
            ]);
            session()->flash('message', 'Tiket berhasil diselesaikan dan ditutup.');
        }

        $this->isEditMode = false;
        $this->reset(['editUnit', 'editLocation', 'editRooms']); // Reset setelah selesai
        $this->modal('closing-modal')->close();
    }

    public function openCancelModal($id)
    {
        $this->selectedTicketId = $id;
        $this->catatan_batal = '';
        $this->modal('cancel-modal')->show();
    }

    public function saveCancel()
    {
        $this->validate([
            'catatan_batal' => 'required|min:5'
        ]);

        $ticket = Ticket::find($this->selectedTicketId);

        // Jangan isi closed_at jika Anda ingin membedakan Closed vs Cancelled
        $ticket->update([
            'status' => 'Cancelled',
            'keterangan_it' => 'Dibatalkan: ' . $this->catatan_batal,
            // Hapus 'closed_at' => now(), agar tidak terdeteksi sebagai tiket selesai
        ]);

        $this->modal('cancel-modal')->close();
        session()->flash('monitor_msg', 'Tiket #' . $this->selectedTicketId . ' telah dibatalkan.');
    }
}; ?>

<div class="space-y-6">
    @if (session()->has('monitor_msg'))
    <div class="p-3 bg-blue-600 text-white rounded-lg text-sm font-bold animate-pulse">
        {{ session('monitor_msg') }}
    </div>
    @endif
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-gray-200 dark:border-neutral-800 shadow-sm space-y-4">
        {{-- Row Atas: Search & Status --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="w-full md:w-1/2">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Cari ID, Judul, Pelapor, atau Lokasi..." />
            </div>

            <div class="flex p-1 bg-slate-100 dark:bg-zinc-800 rounded-lg border border-slate-200 dark:border-zinc-700 w-full md:w-auto overflow-x-auto">
                <button wire:click="$set('statusFilter', '')" class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-widest transition {{ $statusFilter === '' ? 'bg-white dark:bg-zinc-700 shadow-sm text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">Semua</button>
                <button wire:click="$set('statusFilter', 'Open')" class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-widest transition {{ $statusFilter === 'Open' ? 'bg-white dark:bg-zinc-700 shadow-sm text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">Open</button>
                <button wire:click="$set('statusFilter', 'On Progress')" class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-widest transition {{ $statusFilter === 'On Progress' ? 'bg-white dark:bg-zinc-700 shadow-sm text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">Proses</button>
                <button wire:click="$set('statusFilter', 'Closed')" class="flex-1 md:flex-none px-3 py-1.5 rounded-md text-[10px] font-black uppercase tracking-widest transition {{ $statusFilter === 'Closed' ? 'bg-white dark:bg-zinc-700 shadow-sm text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">Selesai</button>
            </div>
        </div>

        {{-- Baris 2: Filter Dinamis --}}
        <div class="flex flex-col md:flex-row gap-3 pt-4 border-t border-gray-100 dark:border-neutral-800">
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

                <div class="w-full md:w-48">
                    <flux:select wire:model.live="staffFilter" icon="user" class="bg-slate-50/50">
                        <flux:select.option value="">Semua Teknisi</flux:select.option>

                        @foreach($it_staffs as $staff)
                        <flux:select.option value="{{ $staff->id }}">{{ $staff->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="w-full md:w-64" wire:ignore>
                    <div x-data="{
        picker: null,
        init() {
            this.picker = flatpickr($refs.datepicker, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                // Sinkronisasi nilai awal jika Livewire sudah memiliki data tanggal
                defaultDate: @json($dateStart && $dateEnd ? [$dateStart, $dateEnd] : []),
                onChange: (selectedDates) => {
                    // Hanya kirim data ke Livewire jika user sudah memilih kedua tanggal (start & end)
                    if (selectedDates.length === 2) {
                        let start = selectedDates[0].toLocaleDateString('sv-SE'); // Format YYYY-MM-DD
                        let end = selectedDates[1].toLocaleDateString('sv-SE');
                        
                        @this.set('dateStart', start);
                        @this.set('dateEnd', end);
                    }
                }
            });

            // Pantau jika ada reset dari Livewire, kosongkan tampilan flatpickr
            $watch('$wire.dateStart', value => {
                if (!value) this.picker.clear();
            });
        }
    }">
                        <flux:input
                            x-ref="datepicker"
                            type="text"
                            placeholder="Pilih rentang tanggal..."
                            icon="calendar"
                            class="bg-slate-50/50" />
                    </div>
                </div>

                @if($dateStart || $dateEnd)
                <div class="flex items-center">
                    <button
                        wire:click="$set('dateStart', ''); $set('dateEnd', '');"
                        class="text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1 bg-red-50 dark:bg-red-950/30 px-2.5 py-1.5 rounded-lg border border-red-200 dark:border-red-900 transition">
                        Reset Tgl
                    </button>
                </div>
                @endif
            </div>

            <flux:button wire:click="exportPdf" icon="printer" variant="outline" class="font-bold text-xs uppercase tracking-tight shadow-sm border-slate-200">
                Export PDF
            </flux:button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-neutral-800 shadow-sm bg-white dark:bg-zinc-900">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-zinc-800/50 border-b dark:border-neutral-800">
                    <tr>
                        <th class="px-6 py-4">Tgl Masuk</th>
                        <th class="px-6 py-4">Info Tiket</th>
                        <th class="px-6 py-4">Pelapor & Lokasi</th>
                        <th class="px-6 py-4">Kategori & Prioritas</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Teknisi</th>
                        <th class="px-6 py-4 text-center">Aksi IT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                    @forelse($all_tickets as $ticket)
                    <tr class="bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $ticket->created_at->translatedFormat('d M Y') }}
                            </div>
                            <div class="text-[11px] text-gray-400 dark:text-gray-500"> {{ $ticket->created_at->translatedFormat('H:i') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900 dark:text-white">#{{ $ticket->id }} - {{ $ticket->subject }}</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">{{ $ticket->created_at->diffForHumans() }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-gray-900 dark:text-gray-200 font-medium">{{ $ticket->user->name }}</div>
                            <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wider">{{ $ticket->location }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $ticket->category }}</span>
                            @if($ticket->priority == 'Cito')
                            <span class="px-2 py-0.5 bg-red-600 dark:bg-red-500/20 text-white dark:text-red-400 text-[10px] rounded-full font-black animate-bounce inline-block">Cito</span>
                            @else
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $ticket->priority }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($ticket->status == 'Open')
                            <span class="px-2 py-1 bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 rounded text-[10px] font-bold border border-red-200 dark:border-red-500/20">OPEN</span>
                            @elseif($ticket->status == 'On Progress')
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded text-[10px] font-bold border border-blue-200 dark:border-blue-500/20">ON PROGRESS</span>
                            @elseif($ticket->status == 'Cancelled')
                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-500/10 text-gray-700 dark:text-gray-400 rounded text-[10px] font-bold border border-gray-200 dark:border-gray-500/20">CANCELLED</span>
                            @else
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 rounded text-[10px] font-bold border border-green-200 dark:border-green-500/20">CLOSED</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($ticket->technician_id)
                            <div class="flex items-center gap-2">
                                <div class="size-7 bg-indigo-100 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 rounded-full flex items-center justify-center text-[10px] font-bold border border-indigo-200 dark:border-indigo-500/20">
                                    {{ strtoupper(substr($ticket->technician->name, 0, 2)) }}
                                </div>
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $ticket->technician->name }}</span>
                            </div>
                            @else
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic font-light tracking-tight">Menunggu Teknisi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                @if($ticket->status == 'Open')
                                @if($ticket->status == 'Open')
                                <div class="flex flex-col items-center gap-2">
                                    {{-- Aksi Utama --}}
                                    <flux:button wire:click="openTakeModal({{ $ticket->id }})" variant="primary" size="sm" class="w-full">
                                        Ambil Tugas
                                    </flux:button>

                                    {{-- Aksi Sekunder (dibuat lebih kecil/halus) --}}
                                    <button wire:click="openCancelModal({{ $ticket->id }})"
                                        class="text-[10px] text-red-500 hover:text-red-700 hover:underline font-medium transition">
                                        Batalkan Tiket
                                    </button>
                                </div>
                                @endif
                                @elseif($ticket->status == 'On Progress')
<<<<<<< HEAD
                                @if( (int) $ticket->technician_id === auth()->id())
=======
                                @if($ticket->technician_id === auth()->id())
>>>>>>> 5c372590fe0c2debcdfef2db7048eb464f64054f
                                <flux:button wire:click="openClosingModal({{ $ticket->id }})" variant="primary" size="sm" class="bg-green-600 hover:bg-green-700 border-none">
                                    Selesaikan
                                </flux:button>
                                @else
                                <span class="text-xs text-gray-400 italic">Dikerjakan teknisi lain</span>
                                @endif
                                @elseif($ticket->status == 'Closed')
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Tombol Lihat Detail (Ubah ke Ikon) --}}
                                    <flux:button
                                        wire:click="showDetail({{ $ticket->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="eye"
                                        inset="top bottom"
                                        v-flux:tooltip="'Lihat Detail'" />

                                    {{-- Tombol Edit Laporan Backdate (Ubah ke Ikon) --}}
                                    <flux:button
                                        wire:click="openEditModal({{ $ticket->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil-square"
                                        inset="top bottom"
                                        class="text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:hover:bg-amber-950/30"
                                        v-flux:tooltip="'Edit Laporan'" />
                                </div>
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
        {{-- Navigasi Pagination Link --}}
        <div class="mt-4 px-2">
            {{ $all_tickets->links() }}
        </div>
    </div> {{-- Penutup tag .overflow-x-auto milik tabel --}}


    <x-tickets.closing-modal
        :list-alat="$listAlat"
        :list-perubahan="$listPerubahan"
        :is-edit-mode="$isEditMode"
        :edit-rooms="$editRooms"
        :edit-unit="$editUnit"
        :edit-location="$editLocation"
        :unit-filter="$unitFilter" />
    <x-tickets.detail-modal :detail-ticket="$detailTicket" />
    <x-take-ticket-modal />
    <x-tickets.cancel-modal />
</div>