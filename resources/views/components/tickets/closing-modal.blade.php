
<flux:modal name="closing-modal" class="md:w-[500px] space-y-6">
    <div>
        <flux:heading size="lg">Form Penyelesaian Tiket</flux:heading>
        <flux:subheading>Lengkapi detail pekerjaan IT untuk menutup tiket.</flux:subheading>
    </div>

    <div class="grid gap-4">
        <div class="grid grid-cols-2 gap-4">
            <flux:select wire:model="kategori_alat" label="Kategori Alat" required>
                <option value="">-- Pilih --</option>
                @foreach($listAlat as $alat)
                    <option value="{{ $alat }}">{{ $alat }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="kategori_perubahan" label="Kategori Perubahan" required>
                <option value="">-- Pilih --</option>
                @foreach($listPerubahan as $perubahan)
                    <option value="{{ $perubahan }}">{{ $perubahan }}</option>
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
        <flux:button wire:click="saveClosing" variant="primary">Simpan & Tutup Tiket</flux:button>
    </div>
</flux:modal>