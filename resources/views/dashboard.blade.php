<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        <!-- Welcome Banner -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-500 p-6 sm:p-8 shadow-md text-white">
            <!-- Decorative elements -->
            <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-10 right-32 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
            
            <div class="relative flex flex-col sm:flex-row items-center gap-6 z-10 text-center sm:text-left">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white p-3 backdrop-blur-md border border-white/20 shadow-inner">
                    <img src="{{ asset('logo.png') }}" alt="iTicket Logo" class="h-full w-full object-contain">
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight drop-shadow-md flex items-center justify-center sm:justify-start gap-2">
                        iTicket Dashboard
                    </h1>
                    <p class="mt-2 text-blue-50 max-w-xl text-sm sm:text-base font-medium opacity-90">
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