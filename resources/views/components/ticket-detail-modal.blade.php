@props(['ticket'])
<flux:modal name="detail-modal" :closable="false" :dismissable="false" class="md:w-[600px] space-y-6">
    @if($ticket)
    <div class="flex justify-between items-start">
        <div>
            <flux:heading size="xl">Detail Tiket #{{ $ticket->id }}</flux:heading>
            <flux:subheading>Diselesaikan oleh: <strong>{{ $ticket->technician->name ?? 'N/A' }}</strong></flux:subheading>
        </div>
        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">CLOSED</span>
    </div>

    <div class="space-y-2 border-l-2 border-indigo-500 pl-4 ml-2">
        <div class="text-xs">
            <span class="text-gray-400">Diajukan:</span>
            <span class="font-medium text-gray-700">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
        </div>

        @if($ticket->taken_at)
        <div class="text-xs">
            <span class="text-gray-400">Mulai Dikerjakan:</span>
            <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($ticket->taken_at)->format('d M Y, H:i') }}</span>
            <span class="text-indigo-500 ml-2">
                ({{ round($ticket->created_at->diffInMinutes($ticket->taken_at, false), 1) }} menit respon)
            </span>
        </div>
        @endif

        @if($ticket->closed_at)
        <div class="text-xs">
            <span class="text-gray-400">Selesai:</span>
            <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($ticket->closed_at)->format('d M Y, H:i') }}</span>
            <span class="text-green-600 ml-2">
                ({{ \Carbon\Carbon::parse($ticket->taken_at)->diffInMinutes($ticket->closed_at, false) }} menit pengerjaan)
            </span>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-6 bg-slate-50 p-5 rounded-xl border border-slate-100">
        <div>
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Kategori Alat</p>
            <p class="text-sm text-gray-800 font-medium">{{ $ticket->kategori_alat ?? 'Belum Diisi' }}</p>
        </div>
        <div>
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Tipe Perubahan</p>
            <p class="text-sm text-gray-800 font-medium">{{ $ticket->kategori_perubahan ?? 'Belum Diisi' }}</p>
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Hasil Tindak Lanjut</p>
            <div class="bg-white p-4 rounded-lg border border-gray-200 text-sm text-gray-700 leading-relaxed shadow-sm">
                {{ $ticket->tindak_lanjut ?? 'Data tindak lanjut tidak ditemukan.' }}
            </div>
        </div>
        <div>
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Hasil Keterangan Tambahan</p>
            <div class="bg-white p-4 rounded-lg border border-gray-200 text-sm text-gray-700 leading-relaxed shadow-sm">
                {{ $ticket->keterangan_it ?? 'Data keterangan tambahan tidak ditemukan.' }}
            </div>
        </div>
    </div>
    @endif

    <div class="flex justify-end pt-4">
        <flux:modal.close>
            <flux:button class="w-full md:w-auto">Tutup</flux:button>
        </flux:modal.close>
    </div>
</flux:modal>