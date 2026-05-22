<flux:modal name="cancel-modal" class="md:w-[400px] space-y-6">
    <div>
        <flux:heading size="lg" class="text-red-600">Batalkan Tiket</flux:heading>
        <flux:subheading>Tiket yang dibatalkan tidak dapat dikerjakan kembali.</flux:subheading>
    </div>

    <flux:textarea 
        wire:model="catatan_batal" 
        label="Alasan Pembatalan" 
        placeholder="Contoh: Tiket duplikat atau salah input..." 
        required />

    <div class="flex gap-2 justify-end">
        <flux:modal.close>
            <flux:button variant="ghost">Batal</flux:button>
        </flux:modal.close>
        <flux:button wire:click="saveCancel" variant="danger">Ya, Batalkan Tiket</flux:button>
    </div>
</flux:modal>