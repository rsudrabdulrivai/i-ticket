<x-layouts::app :title="__('Manajemen Pengguna')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Pengguna</h1>
        </div>

        <div class="flex-1 bg-white dark:bg-zinc-900 rounded-xl border border-neutral-200 dark:border-neutral-800 p-6 overflow-y-auto shadow-sm">
            <livewire:tickets.manage-users />
        </div>
    </div>
</x-layouts::app>