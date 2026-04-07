<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

new class extends Component {
    public $name = '';
    public $email = '';
    public $password = '';
    public $is_it_staff = false;

    // Data yang dilempar ke View
    public function with(): array
    {
        return [
            'users' => User::latest()->get(),
        ];
    }

    // Logika Simpan User
    public function saveUser()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'is_it_staff' => $this->is_it_staff, // Simpan status staff
        ]);

        $this->reset(['name', 'email', 'password', 'is_it_staff']);
        $this->modal('add-user-modal')->close();
        session()->flash('message', 'User berhasil ditambahkan.');
    }

    // Logika Hapus User
    public function deleteUser($id)
    {
        // Proteksi agar tidak menghapus diri sendiri
        if ($id === Auth::id()) {
            session()->flash('error', 'Gak bisa hapus akun sendiri, Ri!');
            return;
        }

        User::find($id)->delete();
        session()->flash('message', 'User telah dihapus.');
    }
}; ?>

<div class="space-y-6">
    @if (session()->has('message'))
    <div class="p-3 bg-green-600 text-white rounded-lg text-sm font-bold animate-pulse">
        {{ session('message') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="p-3 bg-red-600 text-white rounded-lg text-sm font-bold">
        {{ session('error') }}
    </div>
    @endif

    <div class="flex justify-between items-center px-1">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Manajemen Pengguna</h2>
            <p class="text-sm text-gray-500">Kelola staf IT dan user unit rumah sakit.</p>
        </div>

        <flux:modal.trigger name="add-user-modal">
            <flux:button variant="primary" size="sm" icon="user-plus">Tambah User</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4">Nama User</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Status / Role</th>
                    <th class="px-6 py-4">Bergabung</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                <tr class="bg-white hover:bg-slate-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="size-8 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-[10px] font-bold border border-indigo-200">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="font-bold text-gray-900">{{ $user->name }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-600">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($user->is_it_staff)
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-[10px] font-bold border border-indigo-200 uppercase">IT Staff</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded text-[10px] font-bold border border-gray-200 uppercase">User Unit</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-500">{{ $user->created_at->translatedFormat('d M Y') }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            @if($user->id !== Auth::id())
                            <flux:button
                                wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="Yakin ingin menghapus {{ $user->name }}?"
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                class="text-red-600 hover:text-red-700" />
                            @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded text-[10px] font-bold border border-gray-200">ANDA</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-20 text-center text-gray-400 italic text-lg">
                        Belum ada user yang terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <flux:modal name="add-user-modal" class="md:w-[450px] space-y-6">
        <div>
            <flux:heading size="lg">Tambah User Baru</flux:heading>
            <flux:subheading>Input kredensial dan tentukan hak akses user.</flux:subheading>
        </div>

        <form wire:submit="saveUser" class="space-y-4">
            <flux:input wire:model="name" label="Nama Lengkap" placeholder="Masukkan nama..." required />
            <flux:input wire:model="email" type="email" label="Email" placeholder="email@rs.com" required />
            <flux:input wire:model="password" type="password" label="Password" viewable required />
            
            <div class="pt-2 pb-4">
                <flux:checkbox 
                    wire:model="is_it_staff" 
                    label="Berikan Akses Staf IT" 
                    description="Centang jika user ini adalah tim IT yang akan mengelola tiket." 
                />
            </div>

            <div class="flex gap-2 justify-end pt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Simpan User</flux:button>
            </div>
        </form>
    </flux:modal>
</div>