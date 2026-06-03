<flux:modal name="closing-modal" class="md:w-[500px] space-y-6">
    <div>
        {{-- Judul dinamis tergantung status mode edit --}}
        <flux:heading size="lg">{{ $isEditMode ? 'Form Edit Laporan Tiket' : 'Form Penyelesaian Tiket' }}</flux:heading>
        <flux:subheading>{{ $isEditMode ? 'Perbarui detail data penutupan dan lokasi tiket backdate.' : 'Lengkapi detail pekerjaan IT untuk menutup tiket.' }}</flux:subheading>
    </div>

    <div class="grid gap-4">
        {{-- Section Khusus Edit Mode: Penyesuaian Unit & Ruangan --}}
        @if($isEditMode)
        <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900 rounded-xl grid grid-cols-2 gap-3">
            <div>
                {{-- Diubah ke wire:model.live="editUnit" --}}
                <flux:select wire:model.live="editUnit" label="Unit / Bidang" icon="building-office">
                    <flux:select.option value="">-- Pilih Unit --</flux:select.option>
                    @foreach(array_keys(config('options.rooms') ?? []) as $unit)
                    <flux:select.option value="{{ $unit }}">{{ $unit }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                {{-- Diubah ke wire:model.live="editLocation" --}}
                <flux:select wire:model.live="editLocation" label="Lokasi Ruangan" icon="map-pin" :disabled="empty($editRooms)">
                    <flux:select.option value="">{{ $editUnit ? '-- Pilih Ruangan --' : 'Pilih Unit Dulu' }}</flux:select.option>
                    @foreach($editRooms as $room)
                    <flux:select.option value="{{ $room }}">{{ $room }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="col-span-2 text-[11px] text-amber-700 dark:text-amber-400 font-medium italic">
                *Mengubah ruangan di sini akan langsung memperbarui lokasi manifes pada tiket.
            </div>
        </div>
        @endif

        {{-- Detail Pengisian Kerja IT --}}
        <div class="grid grid-cols-2 gap-4">
            <flux:select wire:model="kategori_alat" label="Kategori Alat" required>
                <flux:select.option value="">-- Pilih --</flux:select.option>
                @foreach($listAlat as $alat)
                <flux:select.option value="{{ $alat }}">{{ $alat }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="kategori_perubahan" label="Kategori Perubahan" required>
                <flux:select.option value="">-- Pilih --</flux:select.option>
                @foreach($listPerubahan as $perubahan)
                <flux:select.option value="{{ $perubahan }}">{{ $perubahan }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <flux:textarea wire:model="tindak_lanjut" label="Tindak Lanjut" required placeholder="Contoh: Mengganti cartridge..." />
        <flux:textarea wire:model="keterangan_it" label="Keterangan Tambahan (Opsional)" placeholder="Catatan internal..." />
    </div>

    <div class="flex gap-2 justify-end">
        <flux:modal.close>
            <flux:button variant="ghost">Batal</flux:button>
        </flux:modal.close>

        {{-- Teks tombol dinamis --}}
        <flux:button wire:click="saveClosing" variant="primary">
            {{ $isEditMode ? 'Simpan Perubahan Laporan' : 'Simpan & Tutup Tiket' }}
        </flux:button>
    </div>
</flux:modal>