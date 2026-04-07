<?php

use Livewire\Volt\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $subject = '';
    public $description = '';
    public $location = '';
    public $category = 'Hardware';
    public $priority = 'Medium';
    public $listRuangan = [
        'Rawat Jalan' => ['Poli Anak', 'Poli Dalam', 'Poli Gigi', 'Poli Mata', 'Poli THT', 'Poli Umum'],
        'Rawat Inap'  => ['Bangsal Melati', 'Bangsal Mawar', 'Bangsal Dahlia', 'ICU', 'NICU/PICU'],
        'IGD'         => ['Ruang Triase', 'Ruang Resusitasi', 'Poned'],
        'Manajemen'   => ['Direksi', 'SDM/Kepegawaian', 'Keuangan', 'Rekam Medis', 'Tata Usaha'],
        'Penunjang'   => ['Laboratorium', 'Radiologi', 'Farmasi/Apotek', 'Gizi', 'Gudang Logistik', 'Kamar Jenazah']
    ];

    // Mengambil data tiket terbaru milik user yang login
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

        // Reset input form agar kosong kembali
        $this->reset(['subject', 'description', 'location', 'category', 'priority']);

        session()->flash('message', 'Laporan berhasil dikirim! Tim IT akan segera meluncur.');

        // Tidak perlu redirect agar Livewire bisa update tabel di bawah secara instan
    }
}; ?>

<div class="space-y-10">
    <div class="p-6 bg-white rounded-lg border border-gray-200 shadow-sm">
        <form wire:submit="save">
            @if (session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm font-medium">
                {{ session('message') }}
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Judul Kendala (Misal: Printer Macet)</label>
                    <input type="text" wire:model="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900">
                    @error('subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <flux:select wire:model="location" label="Lokasi / Ruangan" placeholder="Pilih Ruangan..." searchable>
                    <option value="">-- Pilih Lokasi --</option>

                    @foreach($listRuangan as $kategori => $ruangans)
                    <optgroup label="--- {{ strtoupper($kategori) }} ---">
                        @foreach($ruangans as $ruang)
                        <option value="{{ $ruang }}">{{ $ruang }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </flux:select>

                <flux:select wire:model="category" label="Kategori Kendala" placeholder="Pilih Kategori...">
                    <flux:select.option value="Hardware">Hardware (Komputer/Printer)</flux:select.option>
                    <flux:select.option value="Software">Software (Windows/Office)</flux:select.option>
                    <flux:select.option value="Network">Network (Internet/LAN)</flux:select.option>
                    <flux:select.option value="Sistem RS">Sistem RS (SIMRS/Aplikasi)</flux:select.option>
                </flux:select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Detail Masalah</label>
                <textarea wire:model="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-900"></textarea>
                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 font-bold transition duration-200">
                KIRIM LAPORAN KE IT
            </button>
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