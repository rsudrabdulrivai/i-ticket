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
    public $listRuangan = [
        'Rawat Jalan' => ['Poli Anak', 'Poli Dalam', 'Poli Gigi', 'Poli Mata', 'Poli THT', 'Poli Umum'],
        'Rawat Inap'  => ['Bangsal Melati', 'Bangsal Mawar', 'Bangsal Dahlia', 'ICU', 'NICU/PICU'],
        'IGD'         => ['Ruang Triase', 'Ruang Resusitasi', 'Poned'],
        'Manajemen'   => ['Direksi', 'SDM/Kepegawaian', 'Keuangan', 'Rekam Medis', 'Tata Usaha'],
        'Penunjang'   => ['Laboratorium', 'Radiologi', 'Farmasi/Apotek', 'Gizi', 'Gudang Logistik', 'Kamar Jenazah']
    ];

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

                <flux:select wire:model="category" label="Kategori Kendala" placeholder="Pilih Kategori...">
                    <flux:select.option value="Hardware">Hardware (Komputer/Printer)</flux:select.option>
                    <flux:select.option value="Software">Software (Windows/Office)</flux:select.option>
                    <flux:select.option value="Network">Network (Internet/LAN)</flux:select.option>
                    <flux:select.option value="Sistem RS">Sistem RS (SIMRS/Aplikasi)</flux:select.option>
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

    <div class="p-6 bg-white rounded-lg border border-gray-200 shadow-sm">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Riwayat Laporan Saya</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Lokasi</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Teknisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($my_tickets as $ticket)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                            {{ $ticket->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $ticket->subject }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $ticket->location }}
                        </td>
                        <td class="px-4 py-3">
                            @if($ticket->status == 'Open')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Terbuka</span>
                            @elseif($ticket->status == 'On Progress')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Dikerjakan</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Selesai</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($ticket->technician)
                            <span class="text-indigo-600 font-medium">👨‍🔧 {{ $ticket->technician->name }}</span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400 italic">
                            Belum ada riwayat laporan kendala.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>