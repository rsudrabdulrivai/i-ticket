<?php

use Livewire\Volt\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $subject = '';
    public $description = '';
    public $location = '';
    public $selectedKategoriLokasi = '';
    public $category = 'Hardware';
    public $priority = 'Medium';
    public $listRuangan = [];
    // Di dalam class Volt
    public function mount()
    {
        $this->listRuangan = config('options.rooms');
    }

    public function getAvailableRuanganProperty()
    {
        return $this->selectedKategoriLokasi ? $this->listRuangan[$this->selectedKategoriLokasi] : [];
    }

    public function with(): array
    {
        return [
            'my_tickets' => Ticket::where('user_id', Auth::id())
                ->latest()
                ->get(),
        ];
    }

    public function save()
    {
        $this->validate([
            'subject' => 'required|min:5',
            'description' => 'required',
            'location' => 'required',
        ]);

        Ticket::create([
            'user_id' => Auth::id(),
            'subject' => $this->subject,
            'description' => $this->description,
            'location' => $this->location,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => 'Open',
        ]);

        $this->reset(['subject', 'description', 'location', 'category', 'priority']);

        session()->flash('message', 'Laporan berhasil dikirim! Tim IT akan segera meluncur.');
    }
}; ?>

<div class="space-y-10">
    <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
        <form wire:submit="save" class="space-y-6">
            @if (session()->has('message'))
            <div class="p-4 bg-green-50 text-green-700 rounded-lg text-sm font-medium border border-green-200">
                {{ session('message') }}
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input
                    wire:model="subject"
                    label="Judul Kendala"
                    placeholder="Misal: Printer Macet"
                    icon="pencil-square"
                    class="md:col-span-2" />

                <flux:select wire:model="category" label="Kategori Kendala" placeholder="Pilih Kategori...">
                    <flux:select.option value="Hardware">Hardware (Komputer/Printer)</flux:select.option>
                    <flux:select.option value="Software">Software (Windows/Office)</flux:select.option>
                    <flux:select.option value="Network">Network (Internet/LAN)</flux:select.option>
                    <flux:select.option value="Sistem RS">Sistem RS (SIMRS/Aplikasi)</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="selectedKategoriLokasi" label="Bidang" placeholder="Pilih Bidang...">
                    <option value="">-- Pilih Kelompok --</option>
                    @foreach(array_keys($listRuangan) as $kat)
                    <option value="{{ $kat }}">{{ $kat }}</option>
                    @endforeach
                </flux:select>

                <flux:select
                    wire:model="location"
                    label="Nama Ruangan"
                    placeholder="Pilih Ruangan..."
                    :disabled="empty($this->availableRuangan)">
                    <option value="">-- Pilih Ruangan --</option>
                    @foreach($this->availableRuangan as $ruang)
                    <option value="{{ $ruang }}">{{ $ruang }}</option>
                    @endforeach
                </flux:select>

                <flux:textarea
                    wire:model="description"
                    label="Detail Masalah"
                    placeholder="Jelaskan kendala Anda secara detail..."
                    rows="4"
                    class="md:col-span-2" />
            </div>

            <flux:button type="submit" variant="primary" class="w-full py-3" icon="paper-airplane">
                KIRIM LAPORAN KE IT
            </flux:button>
        </form>
    </div>

    <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Riwayat Laporan Saya</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b text-[10px] uppercase font-black text-gray-400 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Judul Kendala</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4">Lokasi</th>
                        <th class="px-6 py-4">Teknisi IT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($my_tickets as $ticket)
                    <tr class="hover:bg-slate-50 transition group">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-medium">
                            {{ $ticket->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $ticket->subject }}</div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-tight">{{ $ticket->category }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center"> @php
                                $badgeColor = match($ticket->status) {
                                'Open' => 'red',
                                'On Progress' => 'blue',
                                'Closed' => 'green',
                                default => 'slate'
                                };

                                $statusLabel = match($ticket->status) {
                                'Open' => 'Terbuka',
                                'On Progress' => 'Dikerjakan',
                                'Closed' => 'Selesai',
                                default => $ticket->status
                                };
                                @endphp
                                <flux:badge color="{{ $badgeColor }}" size="sm" class="font-black uppercase text-[9px] tracking-widest whitespace-nowrap">
                                    {{ $statusLabel }}
                                </flux:badge>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <flux:icon.map-pin size="sm" class="text-gray-300" />
                                <span class="text-xs font-semibold text-gray-600">{{ $ticket->location }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @if($ticket->technician)
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-50 flex items-center justify-center border border-indigo-100">
                                    <flux:icon.user size="xs" class="text-indigo-500" />
                                </div>
                                <span class="text-xs font-bold text-indigo-600">{{ $ticket->technician->name }}</span>
                            </div>
                            @else
                            <div class="flex items-center gap-2 text-gray-400 italic">
                                <flux:icon.clock size="sm" />
                                <span class="text-[11px]">Menunggu Teknisi...</span>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-16 text-center italic text-gray-400 bg-gray-50/50">
                            Belum ada riwayat laporan kendala.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>