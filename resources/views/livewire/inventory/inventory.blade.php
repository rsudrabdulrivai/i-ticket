<?php

use Livewire\Volt\Component;
use App\Models\Inventory;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // Properti Statistik
    public $totalCount = 0;
    public $readyCount = 0;
    public $brokenCount = 0;

    // Properti Form (Sesuai Fillable Model)
    public $asset_code = '';
    public $name = '';
    public $brand = '';
    public $category = 'PC Desktop';
    public $specification = '';
    public $room = '';
    public $status = 'ready';
    public $editingId = null;
    public $listRuangan = [];

    // Properti Filter & Logika Lokasi
    public $search = '';
    public $selectedKategoriLokasi = '';

    public function mount()
    {
        $this->updateStats();
        $this->listRuangan = config('options.rooms');
    }

    public function updateStats()
    {
        $this->totalCount = Inventory::count();
        $this->readyCount = Inventory::where('status', 'ready')->count();
        $this->brokenCount = Inventory::whereIn('status', ['repair', 'broken'])->count();
    }

    public function getAvailableRuanganProperty()
    {
        return $this->selectedKategoriLokasi ? $this->listRuangan[$this->selectedKategoriLokasi] : [];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'items' => Inventory::where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('asset_code', 'like', '%' . $this->search . '%')
                    ->orWhere('room', 'like', '%' . $this->search . '%');
            })
                ->latest()
                ->paginate(10),
        ];
    }

    public function openAddModal()
    {
        $this->reset(['asset_code', 'name', 'brand', 'category', 'specification', 'room', 'status', 'editingId', 'selectedKategoriLokasi']);
        $this->modal('inventory-modal')->show();
    }

    public function edit($id)
    {
        $item = Inventory::findOrFail($id);
        $this->editingId = $item->id;
        $this->asset_code = $item->asset_code;
        $this->name = $item->name;
        $this->brand = $item->brand;
        $this->category = $item->category;
        $this->specification = $item->specification;
        $this->room = $item->room;
        $this->status = $item->status;

        // Mencari kategori lokasi berdasarkan nama ruangan
        foreach ($this->listRuangan as $kategori => $ruangans) {
            if (in_array($item->room, $ruangans)) {
                $this->selectedKategoriLokasi = $kategori;
                break;
            }
        }

        $this->modal('inventory-modal')->show();
    }

    public function save()
    {
        $this->validate([
            'asset_code' => 'required|unique:inventories,asset_code,' . ($this->editingId ?? 'NULL'),
            'name'       => 'required',
            'brand'      => 'required',
            'category'   => 'required',
            'room'       => 'required',
        ]);

        Inventory::updateOrCreate(['id' => $this->editingId], [
            'asset_code'    => $this->asset_code,
            'name'          => $this->name,
            'brand'         => $this->brand,
            'category'      => $this->category,
            'specification' => $this->specification,
            'room'          => $this->room,
            'status'        => $this->status,
        ]);

        $this->updateStats();
        $this->modal('inventory-modal')->close();
        session()->flash('message', $this->editingId ? 'Data aset diperbarui!' : 'Aset baru berhasil didaftarkan.');
    }

    public function delete($id)
    {
        Inventory::find($id)->delete();
        $this->updateStats();
        session()->flash('message', 'Aset telah dihapus dari sistem.');
    }
}; ?>

<div class="space-y-6">
    @if (session()->has('message'))
    <div class="p-3 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-lg animate-pulse">
        {{ session('message') }}
    </div>
    @endif

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 shadow-sm text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Aset IT</p>
            <p class="mt-2 text-4xl font-black text-slate-900">{{ $totalCount }}</p>
        </div>
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 shadow-sm text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kondisi Baik</p>
            <p class="mt-2 text-4xl font-black text-green-600">{{ $readyCount }}</p>
        </div>
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-6 shadow-sm text-center">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Rusak / Repair</p>
            <p class="mt-2 text-4xl font-black text-red-600">{{ $brokenCount }}</p>
        </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Cari Kode Aset, Nama, atau Ruangan..." class="w-full md:w-96" />
        <flux:button wire:click="openAddModal" variant="primary" icon="plus" size="sm">Daftarkan Aset Baru</flux:button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b text-[10px] uppercase font-black text-gray-400 tracking-widest">
                <tr>
                    <th class="px-6 py-4">Informasi Aset</th>
                    <th class="px-6 py-4">Kategori & Brand</th>
                    <th class="px-6 py-4">Bidang / Unit</th>
                    <th class="px-6 py-4">Lokasi Ruangan</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($items as $item)
                <tr class="hover:bg-slate-50 transition group">
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900">{{ $item->name }}</div>
                        <div class="text-[10px] font-mono text-gray-400 tracking-tighter uppercase">{{ $item->asset_code }}</div>
                    </td>

                    <td class="px-6 py-4">
                        <div class="text-xs font-bold text-gray-700">{{ $item->category }}</div>
                        <div class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">{{ $item->brand }}</div>
                    </td>

                    <td class="px-6 py-4">
                        @php
                        $bidang = 'Tidak Diketahui';
                        foreach ($listRuangan as $kategori => $ruangans) {
                        if (in_array($item->room, $ruangans)) {
                        $bidang = $kategori;
                        break;
                        }
                        }

                        $bidangColor = match($bidang) {
                        'Rawat Jalan' => 'text-emerald-600 bg-emerald-50',
                        'Rawat Inap' => 'text-blue-600 bg-blue-50',
                        'IGD' => 'text-red-600 bg-red-50',
                        'Manajemen' => 'text-purple-600 bg-purple-50',
                        'Penunjang' => 'text-amber-600 bg-amber-50',
                        default => 'text-gray-600 bg-gray-50'
                        };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $bidangColor }}">
                            {{ $bidang }}
                        </span>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <flux:icon.map-pin size="sm" class="text-gray-300" />
                            <span class="text-xs font-semibold text-gray-600">{{ $item->room }}</span>
                        </div>
                    </td>

                    <td class="px-6 py-4 text-center">
                        @php
                        $badgeColor = match($item->status) {
                        'ready' => 'green',
                        'used' => 'blue',
                        'repair' => 'yellow',
                        'broken' => 'red',
                        default => 'slate'
                        };
                        @endphp
                        <flux:badge color="{{ $badgeColor }}" size="sm" class="font-black uppercase text-[9px] tracking-widest">
                            {{ $item->status }}
                        </flux:badge>
                    </td>

                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-1">
                            <flux:button wire:click="edit({{ $item->id }})" variant="ghost" size="sm" icon="pencil-square" class="text-blue-600" />
                            <flux:button wire:click="delete({{ $item->id }})" wire:confirm="Hapus data {{ $item->name }}?" variant="ghost" size="sm" icon="trash" class="text-red-500" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-16 text-center italic text-gray-400">Belum ada data inventaris ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>

    <flux:modal name="inventory-modal" class="md:w-[600px] space-y-6">
        <div>
            <flux:heading size="lg">{{ $editingId ? 'Perbarui Data Aset' : 'Registrasi Aset Baru' }}</flux:heading>
            <flux:subheading>Input informasi perangkat keras dengan lengkap sesuai fisik barang.</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="asset_code" label="Kode Aset / SN" required placeholder="ex: RS-IT-001" />
                <flux:input wire:model="name" label="Nama Barang" required placeholder="ex: PC Dell Core i5" />

                <flux:input wire:model="brand" label="Merk / Brand" required placeholder="ex: Dell, HP, Epson" />
                <flux:select wire:model="category" label="Kategori">
                    <option value="PC Desktop">PC Desktop</option>
                    <option value="Laptop">Laptop</option>
                    <option value="Printer">Printer</option>
                    <option value="Jaringan">Infrastruktur Jaringan</option>
                    <option value="Scanner">Scanner</option>
                </flux:select>
            </div>

            <flux:separator text="Detail Penempatan & Kondisi" class="my-2" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model.live="selectedKategoriLokasi" label="Bidang / Unit">
                    <option value="">-- Pilih Bidang --</option>
                    @foreach(array_keys($listRuangan) as $kat)
                    <option value="{{ $kat }}">{{ $kat }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="room" label="Nama Ruangan" :disabled="empty($this->availableRuangan)">
                    <option value="">-- Pilih Ruangan --</option>
                    @foreach($this->availableRuangan as $ruang)
                    <option value="{{ $ruang }}">{{ $ruang }}</option>
                    @endforeach
                </flux:select>
            </div>

            <flux:textarea wire:model="specification" label="Spesifikasi Teknis" placeholder="Tuliskan detail spek (RAM, OS, Processor, dll)" rows="3" />

            <flux:select wire:model="status" label="Kondisi Aset">
                <option value="ready">Ready (Siap Pakai)</option>
                <option value="used">Digunakan</option>
                <option value="repair">Dalam Perbaikan</option>
                <option value="broken">Rusak Total</option>
            </flux:select>

            <div class="flex justify-end gap-2 pt-4 border-t">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Simpan Data</flux:button>
            </div>
        </form>
    </flux:modal>
</div>