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
    public $isBackdate = false;
    public $backdateDate = '';
    public $technician_id = '';
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
            'technicians' => \App\Models\User::where('is_it_staff', true)->get(),
        ];
    }

    public function save()
    {
        $this->validate([
            'subject' => 'required|min:5',
            'description' => 'required',
            'location' => 'required',
            'backdateDate' => 'required_if:isBackdate,true',
            'technician_id' => 'required_if:isBackdate,true',
        ]);

        $ticket = new Ticket([
            'user_id' => Auth::id(),
            'subject' => $this->subject,
            'description' => $this->description,
            'location' => $this->location,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => 'Open',
        ]);

        if ($this->isBackdate && $this->backdateDate) {
            $date = \Carbon\Carbon::parse($this->backdateDate);
            $ticket->created_at = $date;
            $ticket->status = 'Closed';
            $ticket->technician_id = $this->technician_id;
            $ticket->taken_at = $date->copy()->addMinutes(10);
            $ticket->closed_at = $date->copy()->addMinutes(20);
        }

        $ticket->save();

        $this->reset(['subject', 'description', 'location', 'category', 'priority', 'isBackdate', 'backdateDate', 'technician_id']);

        session()->flash('message', 'Laporan berhasil dikirim! Tim IT akan segera meluncur.');
    }
}; ?>

<div class="space-y-10">
    <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-neutral-800 shadow-sm">
        <form wire:submit="save" class="space-y-6">
            @if (session()->has('message'))
            <div class="p-4 bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 rounded-lg text-sm font-medium border border-green-200 dark:border-green-500/20">
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

                <div class="md:col-span-2 p-4 border border-gray-200 dark:border-neutral-700 rounded-lg bg-gray-50/50 dark:bg-zinc-800/50 space-y-4">
                    <flux:switch wire:model.live="isBackdate" label="Laporan Kendala Lampau (Backdate)" description="Aktifkan jika kendala ini terjadi di waktu lampau dan Anda baru melaporkannya sekarang." />

                    @if($isBackdate)
                    <div class="pt-2 space-y-4">
                        <flux:input type="datetime-local" wire:model="backdateDate" label="Waktu Kejadian" />
                        
                        <flux:select wire:model="technician_id" label="Teknisi IT yang Menangani" placeholder="Pilih Teknisi...">
                            <option value="">-- Pilih Teknisi --</option>
                            @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    @endif
                </div>
            </div>

            <flux:button type="submit" variant="primary" class="w-full py-3" icon="paper-airplane">
                KIRIM LAPORAN KE IT
            </flux:button>
        </form>
    </div>

    <div class="p-6 bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-neutral-800 shadow-sm">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Riwayat Laporan Saya</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-zinc-800/50 border-b dark:border-neutral-800 text-[10px] uppercase font-black text-gray-400 dark:text-gray-500 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Judul Kendala</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4">Lokasi</th>
                        <th class="px-6 py-4">Teknisi IT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                    @forelse($my_tickets as $ticket)
                    <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition group">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 font-medium">
                            {{ $ticket->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900 dark:text-white">{{ $ticket->subject }}</div>
                            <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-tight">{{ $ticket->category }}</div>
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
                                <flux:icon.map-pin size="sm" class="text-gray-300 dark:text-gray-600" />
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ $ticket->location }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @if($ticket->technician)
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center border border-indigo-100 dark:border-indigo-500/20">
                                    <flux:icon.user size="xs" class="text-indigo-500 dark:text-indigo-400" />
                                </div>
                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $ticket->technician->name }}</span>
                            </div>
                            @else
                            <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500 italic">
                                <flux:icon.clock size="sm" />
                                <span class="text-[11px]">Menunggu Teknisi...</span>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-16 text-center italic text-gray-400 dark:text-gray-500 bg-gray-50/50 dark:bg-zinc-800/50">
                            Belum ada riwayat laporan kendala.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>