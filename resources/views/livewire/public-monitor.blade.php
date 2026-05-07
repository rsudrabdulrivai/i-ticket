<?php

use Livewire\Volt\Component;
use App\Models\Ticket;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.guest-monitor')]
class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    // Otomatis dipanggil setiap kali variabel $search diubah via wire:model
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Otomatis dipanggil setiap kali variabel $statusFilter diubah
    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Ticket::with(['user', 'technician']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                    ->orWhere('subject', 'like', '%' . $this->search . '%')
                    ->orWhere('location', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($u) {
                        $u->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return [
            'tickets' => $query->latest()->paginate(10),
            'stats' => [
                'total' => Ticket::count(),
                'open' => Ticket::where('status', 'Open')->count(),
                'process' => Ticket::where('status', 'On Progress')->count(),
                'closed' => Ticket::where('status', 'Closed')->count(),
            ]
        ];
    }
}; ?>

<div class="min-h-screen bg-[#0F172A] text-slate-200 font-sans">
    <div class="w-full p-0 space-y-0">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 p-6 bg-slate-900/50 border-b border-slate-700/50">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <span class="p-2 bg-indigo-600 rounded-lg">
                        <x-flux::icon.chart-bar class="size-6" />
                    </span>
                    IT SUPPORT MONITOR <span class="text-indigo-500 underline decoration-indigo-500/30">LIVE</span>
                </h1>
                <p class="text-slate-400 mt-1">Status penanganan gangguan IT secara real-time</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 bg-slate-800/50 p-2 rounded-2xl border border-slate-700">
                    <input wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Cari lokasi/masalah..."
                        class="bg-transparent border-none focus:ring-0 text-sm w-48 md:w-64 placeholder:text-slate-500">
                </div>

                @auth
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                    <x-flux::icon.home class="size-4" />
                    <span>Dashboard</span>
                </a>
                @else
                <a href="{{ route('login') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-bold rounded-xl border border-slate-700 transition-all">
                    <x-flux::icon.arrow-right-start-on-rectangle class="size-4" />
                    <span>Staff Login</span>
                </a>
                @endauth
            </div>
        </div>

        <div class="p-6 space-y-8">

            {{-- Statistik Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([
                ['label' => 'Total Tiket', 'val' => $stats['total'], 'color' => 'indigo', 'icon' => 'ticket'],
                ['label' => 'Menunggu', 'val' => $stats['open'], 'color' => 'red', 'icon' => 'clock'],
                ['label' => 'Diproses', 'val' => $stats['process'], 'color' => 'blue', 'icon' => 'arrow-path'],
                ['label' => 'Selesai', 'val' => $stats['closed'], 'color' => 'green', 'icon' => 'check-circle'],
                ] as $s)
                <div class="bg-slate-800/40 border border-slate-700/50 p-5 rounded-3xl backdrop-blur-sm">
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">{{ $s['label'] }}</p>
                    <div class="flex justify-between items-end mt-2">
                        <h2 class="text-4xl font-black text-{{ $s['color'] }}-500">{{ $s['val'] }}</h2>
                        <div class="p-2 bg-{{ $s['color'] }}-500/10 rounded-xl text-{{ $s['color'] }}-500">
                            {{-- Icon placeholder --}}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Main Table --}}
            <div class="bg-slate-800/30 border border-slate-700/50 rounded-[2rem] overflow-hidden backdrop-blur-md">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-800/60 border-b border-slate-700">
                            <th class="p-5 text-xs font-bold text-slate-400 uppercase">Informasi</th>
                            <th class="p-5 text-xs font-bold text-slate-400 uppercase text-center">Lokasi</th>
                            <th class="p-5 text-xs font-bold text-slate-400 uppercase text-center">Status</th>
                            <th class="p-5 text-xs font-bold text-slate-400 uppercase">Teknisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-700/20 transition-colors group">
                            <td class="p-5">
                                <div class="flex items-center gap-4">
                                    <div class="hidden md:flex size-10 rounded-2xl bg-slate-700 items-center justify-center font-bold text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-lg leading-tight">{{ $ticket->subject }}</h4>
                                        <p class="text-sm text-slate-500">{{ $ticket->created_at->diffForHumans() }} • ID: #{{ $ticket->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-5 text-center">
                                <span class="px-3 py-1 bg-slate-700 text-indigo-400 rounded-full text-xs font-bold border border-slate-600 uppercase tracking-tighter">
                                    {{ $ticket->location }}
                                </span>
                            </td>
                            <td class="p-5 text-center">
                                @php
                                $statusClasses = [
                                'Open' => 'bg-red-500/10 text-red-500 border-red-500/20 animate-pulse',
                                'On Progress' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                'Closed' => 'bg-green-500/10 text-green-500 border-green-500/20'
                                ];
                                @endphp
                                <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border {{ $statusClasses[$ticket->status] ?? '' }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            <td class="p-5">
                                @if($ticket->technician)
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->technician->name) }}&background=6366f1&color=fff" class="size-8 rounded-lg" alt="">
                                    <span class="text-sm font-semibold text-slate-300">{{ $ticket->technician->name }}</span>
                                </div>
                                @else
                                <span class="text-xs text-slate-600 italic">Assigning...</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-20 text-center text-slate-500">
                                Tidak ada aktivitas tiket saat ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Info --}}
            <div class="flex justify-between items-center text-[10px] text-slate-600 font-bold uppercase tracking-[0.2em]">
                <p>Sistem Informasi IT © {{ date('Y') }}</p>
                <p class="flex items-center gap-2">
                    <span class="size-2 bg-green-500 rounded-full shadow-[0_0_8px_#22c55e]"></span>
                    Sistem Berjalan Normal
                </p>
            </div>
        </div>
    </div>