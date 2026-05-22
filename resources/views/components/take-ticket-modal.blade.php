<flux:modal name="take-ticket-modal" title="Pilih Skala Prioritas">
    <div class="space-y-4">
        <p class="text-sm text-gray-600">Silakan tentukan skala prioritas untuk tiket ini sebelum memulai pengerjaan.</p>
        
        <flux:select wire:model="priority" label="Skala Prioritas" placeholder="Pilih prioritas...">
            <flux:select.option value="Low">Low</flux:select.option>
            <flux:select.option value="Medium">Medium</flux:select.option>
            <flux:select.option value="High">High</flux:select.option>
            <flux:select.option value="Cito">Cito</flux:select.option>
        </flux:select>

        <div class="flex justify-end gap-2 pt-4">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            <flux:button wire:click="confirmTakeTicket" variant="primary">Mulai Kerjakan</flux:button>
        </div>
    </div>
</flux:modal>