<?php

use Livewire\Volt\Component;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // Filter & Cari
    public $search = '';
    public $unitFilter = '';
    public $categoryFilter = '';

    // State Form Input Baru
    public $title = '';
    public $unit_owner = 'IT'; 
    public $visibility = 'Public';
    public $category = 'Troubleshooting';
    public $content = '';
    public $tags = '';

    public $listCategory = ['Tutorial', 'Troubleshooting', 'Informasi', 'SOP'];

    public function with(): array
    {
        $query = KnowledgeBase::with('user');

        // Logic Pencarian
        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%')
                  ->orWhere('tags', 'like', '%' . $this->search . '%');
            });
        }

        // Filter Lintas Unit
        if ($this->unitFilter) {
            $query->where('unit_owner', $this->unitFilter);
        }

        // Filter Kategori Keilmuan
        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        // Ambil list unit yang terdaftar di config
        $allUnits = array_keys(config('options.rooms') ?? []);

        return [
            // Tampilkan yang Published, atau Draft milik sendiri
            'articles' => $query->where(function($q) {
                $q->where('status', 'Published')
                  ->orWhere('user_id', Auth::id());
            })->latest()->paginate(9),
            'units' => $allUnits,
        ];
    }

    // Reset pagination jika filter berubah
    public function updatingSearch() { $this->resetPage(); }
    public function updatingUnitFilter() { $this->resetPage(); }
    public function updatingCategoryFilter() { $this->resetPage(); }

    public function openCreateModal()
    {
        $this->reset(['title', 'content', 'tags']);
        $this->modal('create-article-modal')->show();
    }

   public function saveArticle($status = 'Published')
    {
        // 1. Atur Aturan Validasi Dinamis Berdasarkan Status
        if ($status === 'Draft') {
            // Jika cuma simpan draf, minimal ada judul (min 3 karakter) agar bisa di-klik di dashboard
            $this->validate([
                'title' => 'required|min:3|max:255',
                'category' => 'required',
                'unit_owner' => 'required',
            ], [
                'title.required' => 'Draf wajib memiliki judul minimal 3 karakter agar bisa disimpan.',
            ]);
        } else {
            // Jika langsung diterbitkan (Published), validasi wajib ketat dan lengkap
            $this->validate([
                'title' => 'required|min:5|max:255',
                'content' => 'required|min:20',
                'category' => 'required',
                'unit_owner' => 'required',
            ], [
                'title.required' => 'Judul keilmuan wajib diisi.',
                'content.required' => 'Isi materi penjelasan minimal berisi 20 karakter sebelum diterbitkan.',
            ]);
        }
        
        // 2. Eksekusi Penyimpanan ke Database
        KnowledgeBase::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'unit_owner' => $this->unit_owner,
            'visibility' => $this->visibility,
            'category' => $this->category,
            'content' => $this->content ?? '', // Amankan jika konten draf masih kosong nilainya
            'tags' => $this->tags,
            'status' => $status, 
        ]);
        
        // 3. Tutup Modal & Berikan Notifikasi Flash
        $this->modal('create-article-modal')->close();
        
        $msg = $status === 'Draft' ? 'Artikel berhasil disimpan sebagai draf!' : 'Artikel keilmuan baru berhasil diterbitkan!';
        session()->flash('knowledge_msg', $msg);
    }
}; ?>

<div class="space-y-6">
    @if (session()->has('knowledge_msg'))
    <div class="p-3 bg-green-600 text-white rounded-lg text-sm font-bold shadow-sm">
        {{ session('knowledge_msg') }}
    </div>
    @endif

    {{-- Menu Bar Atas --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-gray-200 dark:border-neutral-800 shadow-sm">
        <div>
            <flux:heading size="xl">Bank Knowledge</flux:heading>
            <flux:subheading>Pusat data informasi, tutorial, dan solusi troubleshooting antar unit.</flux:subheading>
        </div>
        
        @if(auth()->user()->is_it_staff)
        <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="font-bold text-xs uppercase tracking-tight">
            Tulis Artikel
        </flux:button>
        @endif
    </div>

    {{-- Filter Pencarian --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari materi keilmuan atau tags..." />
        
        <flux:select wire:model.live="unitFilter" icon="building-office">
            <flux:select.option value="">Semua Unit Pemilik</flux:select.option>
            @foreach($units as $u)
                <flux:select.option value="{{ $u }}">{{ $u }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="categoryFilter" icon="tag">
            <flux:select.option value="">Semua Jenis Materi</flux:select.option>
            @foreach($listCategory as $cat)
                <flux:select.option value="{{ $cat }}">{{ $cat }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- ================= PLACEHOLDER SKELETON SAAT FILTER / CARI DATA ================= --}}
    <div wire:loading.grid wire:target="search, unitFilter, categoryFilter" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @for($i = 0; $i < 6; $i++)
        <div class="animate-pulse bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-gray-100 dark:border-neutral-800 space-y-4 h-[200px]">
            <div class="flex justify-between items-center">
                <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-1/3"></div>
                <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-1/4"></div>
            </div>
            <div class="h-6 bg-gray-200 dark:bg-zinc-800 rounded w-3/4"></div>
            <div class="space-y-2">
                <div class="h-3 bg-gray-200 dark:bg-zinc-800 rounded w-full"></div>
                <div class="h-3 bg-gray-200 dark:bg-zinc-800 rounded w-5/6"></div>
            </div>
        </div>
        @endfor
    </div>

    {{-- Grid Daftar Artikel Utama --}}
    <div wire:loading.remove wire:target="search, unitFilter, categoryFilter" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @forelse($articles as $article)
            @php
                switch($article->category) {
                    case 'Troubleshooting':
                        $bgGradient = 'from-rose-500/5 via-transparent to-transparent';
                        $borderColor = 'group-hover:border-rose-500/50';
                        $badgeColor = 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-500/20';
                        $bgIcon = 'wrench-screwdriver';
                        $iconColor = 'text-rose-500/10 dark:text-rose-500/5';
                        break;
                        
                    case 'Tutorial':
                        $bgGradient = 'from-indigo-500/5 via-transparent to-transparent';
                        $borderColor = 'group-hover:border-indigo-500/50';
                        $badgeColor = 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-100 dark:border-indigo-500/20';
                        $bgIcon = 'academic-cap';
                        $iconColor = 'text-indigo-500/10 dark:text-indigo-500/5';
                        break;
                        
                    case 'SOP':
                        $bgGradient = 'from-emerald-500/5 via-transparent to-transparent';
                        $borderColor = 'group-hover:border-emerald-500/50';
                        $badgeColor = 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-500/20';
                        $bgIcon = 'clipboard-document-check';
                        $iconColor = 'text-emerald-500/10 dark:text-emerald-500/5';
                        break;
                        
                    default:
                        $bgGradient = 'from-amber-500/5 via-transparent to-transparent';
                        $borderColor = 'group-hover:border-amber-500/50';
                        $badgeColor = 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-500/20';
                        $bgIcon = 'information-circle';
                        $iconColor = 'text-amber-500/10 dark:text-amber-500/5';
                        break;
                }
            @endphp

            {{-- CARD DENGAN STYLE GRAFIS BARU --}}
            <div class="relative bg-white dark:bg-zinc-900 bg-gradient-to-br {{ $bgGradient }} p-5 rounded-2xl border border-gray-200 dark:border-neutral-800 shadow-sm flex flex-col justify-between hover:shadow-md transition-all duration-300 group cursor-pointer {{ $borderColor }} overflow-hidden">
                
                {{-- Grafis Latar Belakang --}}
                <div class="absolute -right-6 -top-6 pointer-events-none transition-transform duration-500 group-hover:scale-110 {{ $iconColor }}">
                    <flux:icon name="{{ $bgIcon }}" class="size-28" variant="outline" stroke-width="1" />
                </div>

                {{-- Trik Utama Link Pembungkus Clickable --}}
                <a href="{{ route('knowledge.show', $article->slug) }}" class="absolute inset-0 z-10"></a>

                <div class="space-y-3 relative z-0">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded border {{ $badgeColor }}">
                                {{ $article->category }}
                            </span>
                            
                            @if($article->status === 'Draft')
                                <span class="text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30">
                                    Draft
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] font-bold text-gray-400 bg-gray-50 dark:bg-neutral-800/50 px-2 py-0.5 rounded-md">{{ $article->unit_owner }}</span>
                    </div>
                    
                    <h3 class="font-bold text-gray-900 dark:text-white text-base line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200">
                        {{ $article->title }}
                    </h3>
                    
                    <p class="text-xs text-gray-500 dark:text-gray-400 block [@supports(-webkit-line-clamp:3)]:line-clamp-3 break-all overflow-hidden whitespace-normal leading-relaxed">
                        {{ strip_tags($article->content) }}
                    </p>
                </div>

                <div class="pt-4 mt-4 border-t border-gray-100 dark:border-neutral-800/60 flex justify-between items-center text-[11px] text-gray-400 relative z-0">
                    <span class="flex items-center gap-1">
                        <flux:icon name="user" class="size-3" /> Oleh: <strong class="text-gray-600 dark:text-gray-300 font-semibold">{{ $article->user->name }}</strong>
                    </span>
                    <span>{{ $article->created_at->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-zinc-900 p-8 rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 text-center text-gray-500">
                <flux:icon name="document-text" class="size-8 mx-auto text-gray-400 mb-2" />
                <p class="text-sm font-medium">Belum ada materi keilmuan yang sesuai dengan filter pencarian.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $articles->links() }}
    </div>

    {{-- MODAL FORM PENULISAN --}}
    <flux:modal name="create-article-modal" class="md:w-[650px] space-y-4">
        <div>
            <flux:heading size="lg">Buat Basis Pengetahuan Baru</flux:heading>
            <flux:subheading>Tulis informasi penting, dokumentasi SOP, atau modul perbaikan di sini.</flux:subheading>
        </div>

        <div class="space-y-3">
            <flux:input wire:model="title" label="Judul Materi / Kasus Bug" placeholder="Contoh: Solusi Printer Driver Error 0x000..." required />

            <div class="grid grid-cols-3 gap-3">
                <flux:select wire:model="unit_owner" label="Unit Pemilik">
                    <flux:select.option value="IT">IT (Default)</flux:select.option>
                    @foreach($units as $u)
                        <flux:select.option value="{{ $u }}">{{ $u }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="category" label="Jenis Kategori">
                    @foreach($listCategory as $cat)
                        <flux:select.option value="{{ $cat }}">{{ $cat }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="visibility" label="Akses Pembaca">
                    <flux:select.option value="Public">Public (Semua Unit)</flux:select.option>
                    <flux:select.option value="Internal">Internal (Unit Sendiri)</flux:select.option>
                </flux:select>
            </div>

            <div class="space-y-1" wire:ignore x-data="{
                initQuill() {
                    const quill = new Quill($refs.editor, {
                        theme: 'snow',
                        placeholder: 'Tulis instruksi lengkap, kode perbaikan, atau lampirkan gambar di sini...',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['code-block', 'image', 'link'],
                                ['clean']
                            ]
                        }
                    });
                
                    quill.getModule('toolbar').addHandler('image', () => {
                        const input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/*');
                        input.click();
                
                        input.onchange = () => {
                            const file = input.files[0];
                            if (/^image\//.test(file.type)) {
                                const reader = new FileReader();
                                reader.readAsDataURL(file);
                                reader.onload = (event) => {
                                    const img = new Image();
                                    img.src = event.target.result;
                                    img.onload = () => {
                                        const canvas = document.createElement('canvas');
                                        const ctx = canvas.getContext('2d');
                                        const MAX_WIDTH = 1200;
                                        let width = img.width;
                                        let height = img.height;
                
                                        if (width > MAX_WIDTH) {
                                            height *= MAX_WIDTH / width;
                                            width = MAX_WIDTH;
                                        }
                
                                        canvas.width = width;
                                        canvas.height = height;
                                        ctx.drawImage(img, 0, 0, width, height);
                
                                        const webpBase64 = canvas.toDataURL('image/webp', 0.7);
                                        const range = quill.getSelection();
                                        quill.insertEmbed(range.index, 'image', webpBase64);
                                    };
                                };
                            }
                        };
                    });
                
                    quill.root.innerHTML = @this.get('content');
                
                    quill.on('text-change', () => {
                        @this.set('content', quill.root.innerHTML);
                    });
                }
            }" x-init="initQuill()">
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-1">Materi Penjelasan / Langkah Perbaikan</label>
                
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-neutral-800 rounded-xl overflow-hidden shadow-sm">
                    <div x-ref="editor" class="min-h-[250px] text-sm md:text-base text-gray-800 dark:text-gray-200"></div>
                </div>
            </div>
            
            <flux:input wire:model="tags" label="Tags / Kata Kunci (Opsional)" placeholder="Pisahkan dengan koma, contoh: printer, driver, hardware" />
        </div>

        <div class="flex gap-2 justify-end pt-2 border-t border-gray-100 dark:border-neutral-800 mt-4">
            <flux:modal.close>
                <flux:button variant="ghost">Batal</flux:button>
            </flux:modal.close>
            
            <flux:button wire:click="saveArticle('Draft')" variant="filled" class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 hover:bg-slate-200">
                Simpan Draf
            </flux:button>
            {{-- Sisipkan baris ini tepat di atas deretan tombol Batal / Simpan Draf / Publish --}}
            @if ($errors->any())
                <div class="p-2 bg-rose-50 dark:bg-rose-500/10 text-rose-600 rounded-lg text-xs font-semibold">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <flux:button wire:click="saveArticle('Published')" variant="primary">
                Terbitkan Artikel
            </flux:button>
            
        </div>
    </flux:modal>
</div>