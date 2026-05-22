<flux:modal name="detail-modal" class="md:w-[600px] space-y-6">
    @if($detailTicket)
        <div class="flex justify-between items-start">
            <div>
                <flux:heading size="xl">Detail Tiket #{{ $detailTicket->id }}</flux:heading>
                <flux:subheading>Diselesaikan oleh: <strong>{{ $detailTicket->technician->name ?? 'Tidak diketahui' }}</strong></flux:subheading>
            </div>
            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">CLOSED</span>
        </div>

        {{-- Section Waktu --}}
        <div class="space-y-2 border-l-2 border-indigo-500 pl-4 ml-2">
            <div class="text-xs">
                <span class="text-gray-400">Diajukan:</span>
                <span class="font-medium text-gray-700">{{ $detailTicket->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>

            @if($detailTicket->taken_at)
                <div class="text-xs">
                    <span class="text-gray-400">Mulai Dikerjakan:</span>
                    <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($detailTicket->taken_at)->translatedFormat('d M Y, H:i') }}</span>
                    <span class="text-indigo-500 ml-1">
                        ({{ $detailTicket->created_at->diffAsCarbonInterval($detailTicket->taken_at)->forHumans(['short' => true, 'join' => true]) }} respon)
                    </span>
                </div>
            @endif

            @if($detailTicket->closed_at)
                <div class="text-xs">
                    <span class="text-gray-400">Selesai:</span>
                    <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($detailTicket->closed_at)->translatedFormat('d M Y, H:i') }}</span>
                    <span class="text-green-600 ml-1">
                        ({{ \Carbon\Carbon::parse($detailTicket->taken_at)->diffAsCarbonInterval($detailTicket->closed_at)->forHumans(['short' => true, 'join' => true]) }} pengerjaan)
                    </span>
                </div>
            @endif
        </div>

        {{-- Section Kategori --}}
        <div class="grid grid-cols-2 gap-6 bg-slate-50 p-5 rounded-xl border border-slate-100">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Kategori Alat</p>
                <p class="text-sm font-medium {{ $detailTicket->kategori_alat ? 'text-gray-800' : 'text-gray-400 italic' }}">
                    {{ $detailTicket->kategori_alat ?? 'Tidak ada kategori' }}
                </p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Tipe Perubahan</p>
                <p class="text-sm font-medium {{ $detailTicket->kategori_perubahan ? 'text-gray-800' : 'text-gray-400 italic' }}">
                    {{ $detailTicket->kategori_perubahan ?? 'Tidak ada tipe' }}
                </p>
            </div>
        </div>

        {{-- Section Keterangan dengan Empty State --}}
        <div class="space-y-4">
            @foreach(['Tindak Lanjut' => 'tindak_lanjut', 'Keterangan Tambahan' => 'keterangan_it'] as $label => $field)
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">{{ $label }}</p>
                    @if($detailTicket->$field)
                        <div class="bg-white p-4 rounded-lg border border-gray-200 text-sm text-gray-700 leading-relaxed shadow-sm">
                            {{ $detailTicket->$field }}
                        </div>
                    @else
                        <div class="bg-slate-50 p-4 rounded-lg border border-dashed border-gray-300 text-sm text-gray-400 italic text-center">
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