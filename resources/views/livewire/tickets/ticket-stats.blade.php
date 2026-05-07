<?php

use Livewire\Volt\Component;
use App\Models\Ticket;

new class extends Component {
    // Fungsi untuk menghitung statistik
    public function with(): array
    {
        return [
            'openCount' => Ticket::where('status', 'Open')->count(),
            'processCount' => Ticket::where('status', 'On Progress')->count(),
            'closedCount' => Ticket::where('status', 'Closed')
                             ->whereDate('updated_at', today()) // Khusus yang selesai hari ini
                             ->count(),
        ];
    }
}; ?>

<div wire:poll.30s class="grid gap-4 sm:grid-cols-3">
    <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-4 sm:p-6 shadow-sm flex flex-col justify-center">
        <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Tiket Masuk (Open)</p>
        <div class="mt-2 flex items-baseline gap-2">
            <p class="text-3xl sm:text-4xl font-black text-red-600">{{ $openCount }}</p>
            <span class="text-[10px] sm:text-xs text-gray-400 italic">Butuh penanganan</span>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-4 sm:p-6 shadow-sm flex flex-col justify-center">
        <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Sedang Dikerjakan</p>
        <div class="mt-2 flex items-baseline gap-2">
            <p class="text-3xl sm:text-4xl font-black text-blue-600">{{ $processCount }}</p>
            <span class="text-[10px] sm:text-xs text-gray-400 italic">On progress</span>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-4 sm:p-6 shadow-sm flex flex-col justify-center">
        <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Selesai Hari Ini</p>
        <div class="mt-2 flex items-baseline gap-2">
            <p class="text-3xl sm:text-4xl font-black text-green-600">{{ $closedCount }}</p>
            <span class="text-[10px] sm:text-xs text-gray-400 italic">Target tercapai</span>
        </div>
    </div>
</div>