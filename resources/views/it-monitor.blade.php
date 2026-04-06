<x-layouts::app :title="__('IT Monitoring Center')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <h1 class="text-2xl font-bold">IT Helpdesk Monitor</h1>
        <div class="flex-1 bg-white rounded-xl border border-neutral-200 p-6 overflow-y-auto">
            <livewire:tickets.monitor-tickets />
        </div>
    </div>
</x-layouts::app>