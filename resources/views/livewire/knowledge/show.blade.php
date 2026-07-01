<?php

use Livewire\Volt\Component;
use App\Models\KnowledgeBase;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $slug;
    public $article;

    // Properti Backend untuk State Form & Modal
    public $isEditing = false;
    public $isDeleting = false; 
    public $title;
    public $category;
    public $unit_owner;
    public $visibility;
    public $content;
    public $tags;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->article = KnowledgeBase::with('user')->where('slug', $slug)->firstOrFail();
        $this->article->increment('views_count');

        // Isi data awal sinkronisasi untuk Form Edit
        $this->title = $this->article->title;
        $this->category = $this->article->category;
        $this->unit_owner = $this->article->unit_owner;
        $this->visibility = $this->article->visibility;
        $this->content = $this->article->content;
        $this->tags = $this->article->tags;
    }

    public function publishNow()
    {
        if ($this->article->user_id === Auth::id() || Auth::user()->is_it_staff) {
            $this->article->update(['status' => 'Published']);
            session()->flash('knowledge_msg', 'Artikel resmi diterbitkan dan bisa dibaca semua unit!');
            $this->article = $this->article->fresh();
        }
    }

    public function deleteArticle()
    {
        // Validasi Keamanan Backend: Hanya izinkan jika user yang login adalah IT Staff
        if (Auth::user()->is_it_staff) {
            $this->article->delete();
            return redirect()->route('knowledge.index')->with('knowledge_msg', 'Artikel keilmuan berhasil dihapus secara permanen oleh Tim IT!');
        }
    
        session()->flash('knowledge_error', 'Akses ditolak! Hanya Tim IT yang berhak menghapus artikel keilmuan.');
        $this->isDeleting = false;
    }

    public function updateArticle()
    {
        $this->validate([
            'title' => 'required|min:5|max:255',
            'content' => 'required|min:20',
        ], [
            'title.required' => 'Judul keilmuan wajib diisi.',
            'content.required' => 'Isi materi penjelasan minimal berisi 20 karakter.',
        ]);

        $this->article->update([
            'title' => $this->title,
            'category' => $this->category,
            'unit_owner' => $this->unit_owner,
            'visibility' => $this->visibility,
            'content' => $this->content, 
            'tags' => $this->tags,
        ]);

        $this->isEditing = false;
        session()->flash('knowledge_msg', 'Artikel keilmuan berhasil diperbarui!');
        $this->article = $this->article->fresh();
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">
    {{-- Notifikasi --}}
    @if (session()->has('knowledge_msg'))
    <div class="p-3 bg-green-600 text-white rounded-lg text-sm font-bold shadow-sm">
        {{ session('knowledge_msg') }}
    </div>
    @endif
    
    @if (session()->has('knowledge_error'))
    <div class="p-3 bg-rose-600 text-white rounded-lg text-sm font-bold shadow-sm">
        {{ session('knowledge_error') }}
    </div>
    @endif

    {{-- Tombol Kembali --}}
    <div class="flex items-center justify-between">
        <flux:button href="{{ route('knowledge.index') }}" icon="arrow-left" variant="ghost" size="sm" class="text-gray-500 hover:text-gray-700">
            Kembali ke Bank Knowledge
        </flux:button>
        
        <div class="flex items-center gap-2 text-xs text-gray-400">
            <span class="flex items-center gap-1">
                <flux:icon name="eye" class="size-3.5" /> {{ $article->views_count }} dilihat
            </span>
        </div>
    </div>

    {{-- Konten Utama Dokumen --}}
    <article class="bg-white dark:bg-zinc-900 p-6 md:p-8 rounded-2xl border border-gray-200 dark:border-neutral-800 shadow-sm min-h-[400px] relative">
        
        {{-- ================= 1. SKELETON LOADER (Hanya mendeteksi mount) ================= --}}
        <div wire:loading.flex wire:target="mount" class="animate-pulse space-y-6 w-full flex-col">
            <div class="flex items-center gap-2">
                <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-20"></div>
                <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-32"></div>
            </div>
            <div class="h-8 bg-gray-200 dark:bg-zinc-800 rounded w-3/4 mt-2"></div>
            <div class="h-3 bg-gray-200 dark:bg-zinc-800 rounded w-1/3"></div>
            
            <div class="space-y-3 pt-6 border-t border-gray-100 dark:border-neutral-800 w-full">
                <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-full"></div>
                <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-11/12"></div>
                <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-4/5"></div>
                <div class="h-52 bg-gray-200 dark:bg-zinc-800 rounded-xl w-full my-4"></div>
                <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-2/3"></div>
            </div>
        </div>

        {{-- ================= 2. KONTEN UTAMA ASLI (Aman saat edit/delete) ================= --}}
        <div wire:loading.remove wire:target="mount" class="space-y-6">
            {{-- Meta Data Atas --}}
            <div class="space-y-3 border-b border-gray-100 dark:border-neutral-800 pb-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest px-2.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">
                        {{ $article->category }}
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Milik Unit: <strong>{{ $article->unit_owner }}</strong></span>
                    <span class="text-gray-300 dark:text-neutral-700">•</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Akses: <strong>{{ $article->visibility }}</strong></span>
                    
                    @if($article->status === 'Draft')
                        <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">
                            Draft
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white tracking-tight leading-tight">
                    {{ $article->title }}
                </h1>

                <div class="flex items-center gap-2 text-xs text-gray-400 pt-1">
                    <span>Ditulis oleh: <strong class="text-gray-700 dark:text-gray-300">{{ $article->user->name }}</strong></span>
                    <span>•</span>
                    <span>Diterbitkan: {{ $article->created_at->translatedFormat('d F Y, H:i') }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-dashed border-gray-100 dark:border-neutral-800 mt-3">
                    {{-- Tombol Terbitkan --}}
                    @if($article->status === 'Draft' && ($article->user_id === auth()->id() || auth()->user()->is_it_staff))
                        <flux:button wire:click="publishNow" variant="primary" size="sm" icon="cloud-arrow-up" class="text-xs font-bold uppercase tracking-tight">
                            Terbitkan Sekarang
                        </flux:button>
                    @endif
                
                    {{-- Tombol Edit --}}
                    @if($article->user_id === auth()->id() || auth()->user()->is_it_staff)
                        <flux:button wire:click="$set('isEditing', true)" variant="ghost" size="sm" icon="pencil-square" class="text-amber-500 hover:text-amber-600 text-xs">
                            Edit Materi
                        </flux:button>
                    @endif
                
                    {{-- Tombol Hapus Khusus IT --}}
                    @if(auth()->user()->is_it_staff)
                        <flux:button wire:click="$set('isDeleting', true)" variant="ghost" size="sm" icon="trash" class="text-rose-500 hover:text-rose-600 text-xs">
                            Hapus
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- Isi Materi Dokumen HTML --}}
            <div class="prose prose-sm md:prose-base dark:prose-invert max-w-none text-gray-800 dark:text-gray-200 focus:outline-none break-words">
                {!! $article->content !!}
            </div>

            {{-- Tags --}}
            @if($article->tags)
            <div class="pt-6 border-t border-gray-100 dark:border-neutral-800 flex flex-wrap gap-1.5 items-center">
                <span class="text-xs text-gray-400 font-bold mr-1">Tags:</span>
                @foreach(explode(',', $article->tags) as $tag)
                    <span class="text-xs bg-slate-100 dark:bg-zinc-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-zinc-700 font-medium">
                        #{{ trim($tag) }}
                    </span>
                @endforeach
            </div>
            @endif
        </div>
    </article>

    {{-- MODAL INTERAKTIF FORM EDIT --}}
    <flux:modal wire:model="isEditing" class="md:w-[650px] space-y-4">
        <div>
            <flux:heading size="lg">Edit Basis Pengetahuan</flux:heading>
            <flux:subheading>Perbarui data tutorial atau dokumentasi sistem.</flux:subheading>
        </div>

        <div class="space-y-3">
            <flux:input wire:model="title" label="Judul Materi / Kasus Bug" required />

            <div class="grid grid-cols-2 gap-3">
                <flux:select wire:model="category" label="Jenis Kategori">
                    <flux:select.option value="Tutorial">Tutorial</flux:select.option>
                    <flux:select.option value="Troubleshooting">Troubleshooting</flux:select.option>
                    <flux:select.option value="Informasi">Informasi</flux:select.option>
                    <flux:select.option value="SOP">SOP</flux:select.option>
                </flux:select>

                <flux:select wire:model="visibility" label="Akses Pembaca">
                    <flux:select.option value="Public">Public (Semua Unit)</flux:select.option>
                    <flux:select.option value="Internal">Internal (Unit Sendiri)</flux:select.option>
                </flux:select>
            </div>

            <div class="space-y-1" wire:ignore x-data="{
                initQuillEdit() {
                    const quillEdit = new Quill($refs.editorEdit, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, false] }],
                                ['bold', 'italic', 'underline'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['code-block', 'image'],
                                ['clean']
                            ]
                        }
                    });
                    
                    quillEdit.getModule('toolbar').addHandler('image', () => {
                        const input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/*');
                        input.click();
                        input.onchange = () => {
                            const file = input.files[0];
                            const reader = new FileReader();
                            reader.readAsDataURL(file);
                            reader.onload = (e) => {
                                const img = new Image();
                                img.src = e.target.result;
                                img.onload = () => {
                                    const canvas = document.createElement('canvas');
                                    const ctx = canvas.getContext('2d');
                                    canvas.width = img.width > 1200 ? 1200 : img.width;
                                    canvas.height = img.width > 1200 ? img.height * (1200 / img.width) : img.height;
                                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                                    const webpBase64 = canvas.toDataURL('image/webp', 0.7);
                                    const range = quillEdit.getSelection();
                                    quillEdit.insertEmbed(range.index, 'image', webpBase64);
                                };
                            };
                        };
                    });

                    quillEdit.on('text-change', () => {
                        @this.set('content', quillEdit.root.innerHTML);
                    });
                }
            }" x-init="initQuillEdit()">
                <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-1">Materi Penjelasan</label>
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-neutral-800 rounded-xl overflow-hidden shadow-sm">
                    <div x-ref="editorEdit" class="min-h-[250px] text-sm md:text-base text-gray-800 dark:text-gray-200">{!! $article->content !!}</div>
                </div>
            </div>
            
            <flux:input wire:model="tags" label="Tags / Kata Kunci" />
        </div>

        <div class="flex gap-2 justify-end pt-2 border-t border-gray-100 dark:border-neutral-800 mt-4">
            <flux:button wire:click="$set('isEditing', false)" variant="ghost">Batal</flux:button>
            <flux:button wire:click="updateArticle" variant="primary">Simpan Perubahan</flux:button>
        </div>
    </flux:modal>

    {{-- ================= BARU: MODAL VALIDASI KONFIRMASI HAPUS ================= --}}
    <flux:modal wire:model="isDeleting" class="w-full max-w-md space-y-4">
        <div class="flex items-start gap-3">
            <div class="p-2 bg-rose-50 dark:bg-rose-500/10 text-rose-600 rounded-lg shrink-0">
                <flux:icon name="exclamation-triangle" class="size-6" />
            </div>
            <div>
                <flux:heading size="lg">Hapus Artikel Keilmuan?</flux:heading>
                <flux:subheading class="mt-1">
                    Tindakan ini tidak dapat dibatalkan. Artikel berjudul <strong class="text-gray-900 dark:text-white">"{{ $article->title }}"</strong> akan dihapus secara permanen.
                </flux:subheading>
            </div>
        </div>

        <div class="flex gap-2 justify-end pt-2 border-t border-gray-100 dark:border-neutral-800 mt-4">
            <flux:button wire:click="$set('isDeleting', false)" variant="ghost">
                Tidak, Batalkan
            </flux:button>
            <flux:button wire:click="deleteArticle" class="bg-rose-600 hover:bg-rose-700 text-white font-bold">
                Ya, Hapus Permanen
            </flux:button>
        </div>
    </flux:modal>
</div>

{{-- Letakkan kode ini di baris paling akhir file show.blade.php kamu --}}
<style>
    /* Styling Kustom Khusus untuk Menjinakkan Code Block bawaan Quill.js */
    .prose pre.ql-syntax {
        background-color: #18181b !important; /* Warna zinc-900 biar pas dengan tema */
        color: #f4f4f5 !important;            /* Warna teks zinc-100 */
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
        padding: 1rem !important;
        border-radius: 0.75rem !important;
        overflow-x: auto !important;
        margin-top: 1rem !important;
        margin-bottom: 1rem !important;
        border: 1px solid #27272a !important; /* Border zinc-800 tipis */
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
    }

    /* Style tambahan jika ada tag inline code biasa (opsional) */
    .prose code {
        background-color: #f4f4f5;
        color: #da2777;
        padding: 0.2rem 0.4rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    
    .dark .prose code {
        background-color: #27272a;
        color: #f472b6;
    }
</style>