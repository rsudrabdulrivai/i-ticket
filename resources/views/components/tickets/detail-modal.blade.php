<flux:modal name="detail-modal" class="md:w-[600px] space-y-6">
    @if($detailTicket)
        {{-- Header Modal --}}
        <div class="flex justify-between items-start border-b border-gray-100 dark:border-neutral-800 pb-4">
            <div>
                <flux:heading size="xl">Detail Tiket #{{ $detailTicket->id }}</flux:heading>
                <flux:subheading>Diselesaikan oleh: <strong>{{ $detailTicket->technician->name ?? 'Tidak diketahui' }}</strong></flux:subheading>
            </div>
            <span class="px-3 py-1 bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400 text-xs font-bold rounded-full border border-green-200 dark:border-green-500/20">CLOSED</span>
        </div>

        {{-- BARU: Informasi Manifes Utama Tiket (Judul, Pelapor, Ruangan, dll) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-gray-200/60 dark:border-neutral-800/60 text-sm">
            <div class="sm:col-span-2">
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-0.5">Judul Permasalahan / Subjek</p>
                <p class="font-bold text-gray-900 dark:text-white text-base">{{ $detailTicket->subject }}</p>
            </div>

            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-0.5">User Pelapor</p>
                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $detailTicket->user->name ?? 'User Terhapus' }}</p>
            </div>

            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-0.5">Lokasi Ruangan</p>
                <span class="inline-block text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-100 dark:border-indigo-500/20">
                    {{ $detailTicket->location }}
                </span>
            </div>

            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-0.5">Kategori Utama</p>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $detailTicket->category ?? '-' }}</p>
            </div>

            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-0.5">Skala Prioritas</p>
                @if($detailTicket->priority == 'Cito')
                    <span class="px-2 py-0.5 bg-red-600 text-white text-[10px] rounded-full font-black inline-block uppercase tracking-wide">Cito</span>
                @else
                    <span class="font-bold text-gray-700 dark:text-gray-300">{{ $detailTicket->priority }}</span>
                @endif
            </div>
            
            @if($detailTicket->description)
            <div class="sm:col-span-2 pt-2 border-t border-gray-100 dark:border-neutral-800/60">
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Deskripsi Keluhan Awal</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 bg-white dark:bg-zinc-900 p-2.5 rounded-lg border border-gray-200 dark:border-neutral-800 leading-relaxed">{{ $detailTicket->description }}</p>
            </div>
            @endif
        </div>

        {{-- Section Waktu Pengerjaan --}}
        <div class="space-y-2 border-l-2 border-indigo-500 pl-4 ml-2">
            <div class="text-xs">
                <span class="text-gray-400">Diajukan:</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $detailTicket->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>

            @if($detailTicket->taken_at)
                <div class="text-xs">
                    <span class="text-gray-400">Mulai Dikerjakan:</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($detailTicket->taken_at)->translatedFormat('d M Y, H:i') }}</span>
                    <span class="text-indigo-500 dark:text-indigo-400 ml-1 font-semibold">
                        ({{ $detailTicket->created_at->diffAsCarbonInterval($detailTicket->taken_at)->forHumans(['short' => true, 'join' => true]) }} respon)
                    </span>
                </div>
            @endif

            @if($detailTicket->closed_at)
                <div class="text-xs">
                    <span class="text-gray-400">Selesai:</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($detailTicket->closed_at)->translatedFormat('d M Y, H:i') }}</span>
                    <span class="text-green-600 dark:text-green-400 ml-1 font-semibold">
                        ({{ \Carbon\Carbon::parse($detailTicket->taken_at)->diffAsCarbonInterval($detailTicket->closed_at)->forHumans(['short' => true, 'join' => true]) }} pengerjaan)
                    </span>
                </div>
            @endif
        </div>

        {{-- Section Analisis Kategori IT --}}
        <div class="grid grid-cols-2 gap-6 bg-slate-50 dark:bg-zinc-800/30 p-5 rounded-xl border border-slate-100 dark:border-neutral-800">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Kategori Alat</p>
                <p class="text-sm font-medium {{ $detailTicket->kategori_alat ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400 italic' }}">
                    {{ $detailTicket->kategori_alat ?? 'Tidak ada kategori' }}
                </p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Tipe Perubahan</p>
                <p class="text-sm font-medium {{ $detailTicket->kategori_perubahan ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400 italic' }}">
                    {{ $detailTicket->kategori_perubahan ?? 'Tidak ada tipe' }}
                </p>
            </div>
        </div>

        {{-- Section Hasil Kerja / Tindak Lanjut --}}
        <div class="space-y-4">
            @foreach(['Tindak Lanjut' => 'tindak_lanjut', 'Keterangan Tambahan' => 'keterangan_it'] as $label => $field)
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">{{ $label }}</p>
                    @if($detailTicket->$field)
                        <div class="bg-white dark:bg-zinc-900 p-4 rounded-lg border border-gray-200 dark:border-neutral-800 text-sm text-gray-700 dark:text-gray-300 leading-relaxed shadow-sm">
                            {{ $detailTicket->$field }}
                        </div>
                    @else
                        <div class="bg-slate-50 dark:bg-zinc-800/10 p-4 rounded-lg border border-dashed border-gray-300 dark:border-neutral-800 text-sm text-gray-400 italic text-center">
                            Tidak ada catatan untuk {{ strtolower($label) }}.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="p-8 text-center text-gray-400">
            Memuat data tiket...
        </div>
    @endif

    <div class="flex justify-end pt-4">
        <flux:modal.close>
            <flux:button variant="ghost">Tutup</flux:button>
        </flux:modal.close>
    </div>
</flux:modal>