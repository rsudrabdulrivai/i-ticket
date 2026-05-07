<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <!-- Welcome Banner -->
        <div class="relative overflow-hidden rounded-xl bg-zinc-900 p-6 sm:p-8 shadow-xl text-white border border-zinc-800">
            <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-indigo-500/10 blur-3xl"></div>
            <div class="absolute -bottom-10 right-32 h-32 w-32 rounded-full bg-zinc-500/10 blur-2xl"></div>

            <div class="relative flex flex-col sm:flex-row items-center gap-6 z-10 text-center sm:text-left">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white p-3 backdrop-blur-md border border-zinc-700 shadow-2xl">
                    <img src="{{ asset('logo.png') }}" alt="iTicket Logo" class="h-full w-full object-contain brightness-110">
                </div>

                <div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center justify-center sm:justify-start gap-2 text-zinc-50">
                        iTicket Dashboard
                    </h1>
                    <p class="mt-2 text-zinc-400 max-w-xl text-sm sm:text-base font-medium leading-relaxed">
                        Sistem Informasi Pelaporan Kendala IT. Laporkan masalah Anda dengan mudah, dan tim IT kami akan segera meluncur untuk menanganinya!
                    </p>
                </div>
            </div>
        </div>
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