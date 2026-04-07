<x-layouts::app :title="__('Inventory Asset IT')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Inventaris IT</h1>
        </div>

        <div class="flex-1 bg-white rounded-xl border border-neutral-200 p-6 overflow-y-auto shadow-sm">
            <livewire:inventory.inventory />
        </div>
    </div>
</x-layouts::app>