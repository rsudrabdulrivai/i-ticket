@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        {{-- Tampilan Mobile (Simple Kiri Kanan) --}}
        <div class="flex justify-between flex-1 md:hidden">
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-800/40 border border-slate-700/50 rounded-xl cursor-default">Previous</span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" class="px-4 py-2 text-xs font-bold text-slate-300 bg-slate-800 border border-slate-700 hover:bg-slate-700 rounded-xl transition-colors">Previous</button>
            @endif

            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" class="px-4 py-2 text-xs font-bold text-slate-300 bg-slate-800 border border-slate-700 hover:bg-slate-700 rounded-xl transition-colors">Next</button>
            @else
                <span class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-800/40 border border-slate-700/50 rounded-xl cursor-default">Next</span>
            @endif
        </div>

        {{-- Tampilan Desktop / Monitor Standby --}}
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
           <div>
            <p class="text-xs text-slate-400">
                Menampilkan <span class="font-bold text-slate-200">{{ $paginator->firstItem() }}</span> sampai <span class="font-bold text-slate-200">{{ $paginator->lastItem() }}</span> dari <span class="font-bold text-slate-200">{{ $paginator->total() }}</span> aktivitas
            </p>
        </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm rounded-xl overflow-hidden border border-slate-700/60">
                    {{-- Tombol Previous --}}
                @if ($paginator->onFirstPage())
                    <span style="padding: 10px 14px; color: #475569; cursor: default; display: inline-block;">
                        <x-flux::icon.chevron-left class="size-4" />
                    </span>
                @else
                    <button wire:click="previousPage" rel="prev" style="padding: 10px 14px; color: #94a3b8; background: transparent; border: none; cursor: pointer;" class="hover:bg-slate-700 hover:text-white transition-colors">
                        <x-flux::icon.chevron-left class="size-4" />
                    </button>
                @endif
            
                {{-- Elemen Angka --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span style="padding: 8px 14px; color: #475569; font-size: 13px;">{{ $element }}</span>
                    @endif
            
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                {{-- Halaman Aktif: Diberikan background indigo kuat & padding lebar pasti --}}
                                <span style="padding: 8px 16px; bg-color: #4f46e5; background-color: #4f46e5; color: #ffffff; font-weight: bold; font-size: 13px; display: inline-block;">
                                    {{ $page }}
                                </span>
                            @else
                                {{-- Halaman Tidak Aktif: Diberikan padding lebar pasti agar tidak berdempetan --}}
                                <button wire:click="gotoPage({{ $page }})" style="padding: 8px 16px; color: #94a3b8; background: transparent; border: none; font-size: 13px; cursor: pointer;" class="hover:bg-slate-700 hover:text-white transition-colors">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            
                {{-- Tombol Next --}}
                @if ($paginator->hasMorePages())
                    <button wire:click="nextPage" rel="next" style="padding: 10px 14px; color: #94a3b8; background: transparent; border: none; cursor: pointer;" class="hover:bg-slate-700 hover:text-white transition-colors">
                        <x-flux::icon.chevron-right class="size-4" />
                    </button>
                @else
                    <span style="padding: 10px 14px; color: #475569; cursor: default; display: inline-block;">
                        <x-flux::icon.chevron-right class="size-4" />
                    </span>
                @endif
                </span>
            </div>
        </div>
    </nav>
@endif