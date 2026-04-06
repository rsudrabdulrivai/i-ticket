<x-layouts::app :title="__('Dashboard IT Support')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        <livewire:tickets.ticket-stats />

        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white shadow-sm overflow-y-auto">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Buat Pengaduan Kendala IT</h2>
                <hr class="mb-6 border-neutral-100">
                
                <livewire:tickets.create-ticket />
            </div>
        </div>
        
    </div>
</x-layouts::app>