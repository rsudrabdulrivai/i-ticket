<?php

use Livewire\Volt\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component {
    public $selectedTicketId;
    public $tindak_lanjut = '';
    public $keterangan_it = '';
    public $kategori_perubahan = '';
    public $kategori_alat = '';

    // Tambahkan properti ini untuk menampung data detail
    public $detailTicket;

    public $listPerubahan = ['Perbaikan Ringan', 'Penggantian Komponen', 'Update Konfigurasi', 'Edukasi User', 'Lain-lain'];
    public $listAlat = ['Komputer/PC', 'Printer/Scanner', 'Jaringan/Internet', 'Aplikasi/SIMRS', 'Lain-lain'];

    public function with(): array
    {
        return [
            'all_tickets' => Ticket::with(['user', 'technician'])
                ->latest()
                ->get(),
        ];
    }

    public function updateStatus($id, $status)
    {
        $ticket = Ticket::find($id);
        $data = ['status' => $status];

        if ($status === 'On Progress') {
            $data['technician_id'] = Auth::id();
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

    public function saveClosing()
    {
        $ticket = Ticket::find($this->selectedTicketId);

        $ticket->update([
            'status' => 'Closed',
            'tindak_lanjut' => $this->tindak_lanjut,
            'keterangan_it' => $this->keterangan_it,
            'kategori_perubahan' => $this->kategori_perubahan,
            'kategori_alat' => $this->kategori_alat,
        ]);

        $this->modal('closing-modal')->close();
        session()->flash('monitor_msg', 'Tiket #' . $this->selectedTicketId . ' Berhasil Diselesaikan!');
    }
}; ?>

<div class="space-y-6">
    @if (session()->has('monitor_msg'))
    <div class="p-3 bg-blue-600 text-white rounded-lg text-sm font-bold animate-pulse">
        {{ session('monitor_msg') }}
    </div>
    @endif

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

    <flux:modal name="closing-modal" class="md:w-[500px] space-y-6">
        <div>
            <flux:heading size="lg">Form Penyelesaian Tiket</flux:heading>
            <flux:subheading>Lengkapi detail pekerjaan IT untuk menutup tiket.</flux:subheading>
        </div>

        <div class="grid gap-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="kategori_alat" label="Kategori Alat">
                    <option value="">-- Pilih --</option>
                    @foreach($listAlat as $alat)
                    <option value="{{ $alat }}">{{ $alat }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="kategori_perubahan" label="Kategori Perubahan">
                    <option value="">-- Pilih --</option>
                    @foreach($listPerubahan as $perubahan)
                    <option value="{{ $perubahan }}">{{ $perubahan }}</option>
                    @endforeach
                </flux:select>
            </div>

            <flux:textarea wire:model="tindak_lanjut" label="Tindak Lanjut" placeholder="Apa yang Anda lakukan untuk memperbaiki ini?" />
            <flux:textarea wire:model="keterangan_it" label="Keterangan Tambahan" placeholder="Catatan internal IT (opsional)..." />
        </div>

        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button wire:click="saveClosing" variant="primary">Simpan & Tutup Tiket</flux:button>
        </div>
    </flux:modal>

    <flux:modal name="detail-modal" class="md:w-[600px] space-y-6">
        @if($detailTicket)
        <div class="flex justify-between items-start">
            <div>
                <flux:heading size="xl">Detail Tiket #{{ $detailTicket->id }}</flux:heading>
                <flux:subheading>Diselesaikan oleh: <strong>{{ $detailTicket->technician->name }}</strong></flux:subheading>
            </div>
            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">CLOSED</span>
        </div>

        <div class="grid grid-cols-2 gap-6 bg-slate-50 p-5 rounded-xl border border-slate-100">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Kategori Alat</p>
                <p class="text-sm text-gray-800 font-medium">{{ $detailTicket->kategori_alat ?? 'Belum Diisi' }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Tipe Perubahan</p>
                <p class="text-sm text-gray-800 font-medium">{{ $detailTicket->kategori_perubahan ?? 'Belum Diisi' }}</p>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Hasil Tindak Lanjut</p>
                <div class="bg-white p-4 rounded-lg border border-gray-200 text-sm text-gray-700 leading-relaxed shadow-sm">
                    {{ $detailTicket->tindak_lanjut ?? 'Data tindak lanjut tidak ditemukan.' }}
                </div>
            </div>
             <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Hasil Keterangan Tambahan</p>
                <div class="bg-white p-4 rounded-lg border border-gray-200 text-sm text-gray-700 leading-relaxed shadow-sm">
                    {{ $detailTicket->keterangan_it ?? 'Data keterangan tambahan tidak ditemukan.' }}
                </div>
            </div>
        </div>
        @endif

        <div class="flex justify-end pt-4">
            <flux:modal.close>
                <flux:button class="w-full md:w-auto">Tutup Detail</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>
</div>