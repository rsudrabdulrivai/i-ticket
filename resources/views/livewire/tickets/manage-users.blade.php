<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    public $search = '';
    public $name = '';
    public $email = '';
    public $password = '';
    public $is_it_staff = false;
    public $editingUserId = null;

    public function with(): array
    {
        return [
            'users' => User::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->latest()
                ->get(),
        ];
    }

    public function openAddModal()
    {
        $this->reset(['name', 'email', 'password', 'is_it_staff', 'editingUserId']);
        $this->modal('user-modal')->show();
    }

    public function editUser($id)
    {
        $user = User::find($id);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_it_staff = (bool) $user->is_it_staff;
        $this->password = ''; // Kosongkan password saat edit

        $this->modal('user-modal')->show();
    }

    public function saveUser()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . ($this->editingUserId ?? 'NULL'),
            'password' => $this->editingUserId ? 'nullable|min:6' : 'required|min:6',
        ]);

        if ($this->editingUserId) {
            $user = User::find($this->editingUserId);
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'is_it_staff' => $this->is_it_staff,
            ];
            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }
            $user->update($data);
            $message = 'User berhasil diperbarui.';
        } else {
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'is_it_staff' => $this->is_it_staff,
            ]);
            $message = 'User berhasil ditambahkan.';
        }

        $this->modal('user-modal')->close();
        session()->flash('message', $message);
    }

    public function deleteUser($id)
    {
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

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-1">
        <div class="w-full md:w-72">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Cari nama atau email..."
                clearable />
        </div>

        <flux:button
            wire:click="openAddModal"
            variant="primary"
            icon="user-plus"
            class="w-full md:w-auto">
            Tambah User
        </flux:button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-neutral-800 shadow-sm bg-white dark:bg-zinc-900">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-50 dark:bg-zinc-800/50 border-b dark:border-neutral-800">
                <tr>
                    <th class="px-6 py-4">Nama User</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Status / Role</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                @forelse($users as $user)
                <tr class="bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="size-8 bg-indigo-100 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 rounded-full flex items-center justify-center text-[10px] font-bold border border-indigo-200 dark:border-indigo-500/20">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-600 dark:text-gray-300">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($user->is_it_staff)
                        <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 rounded text-[10px] font-bold border border-indigo-200 dark:border-indigo-500/20 uppercase">IT Staff</span>
                        @else
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400 rounded text-[10px] font-bold border border-gray-200 dark:border-gray-500/20 uppercase">User Unit</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <flux:button
                                wire:click="editUser({{ $user->id }})"
                                variant="ghost"
                                size="sm"
                                icon="pencil-square"
                                class="text-blue-600" />

                            @if($user->id !== Auth::id())
                            <flux:button
                                wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="Yakin ingin menghapus {{ $user->name }}?"
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                class="text-red-600 hover:text-red-700" />
                            @else
                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-500/10 text-gray-500 dark:text-gray-400 rounded text-[10px] font-bold border border-gray-200 dark:border-gray-500/20 uppercase flex items-center justify-center">Anda</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-20 text-center text-gray-400 italic text-lg">
                        Belum ada user yang terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <flux:modal name="user-modal" class="md:w-[450px] space-y-6">
        <div>
            <flux:heading size="lg">{{ $editingUserId ? 'Edit User' : 'Tambah User Baru' }}</flux:heading>
            <flux:subheading>{{ $editingUserId ? 'Perbarui informasi user atau hak aksesnya.' : 'Input kredensial dan tentukan hak akses user.' }}</flux:subheading>
        </div>

        <form wire:submit="saveUser" class="space-y-4">
            <flux:input wire:model="name" label="Nama Lengkap" placeholder="Masukkan nama..." required />
            <flux:input wire:model="email" type="email" label="Email" placeholder="email@rs.com" required />

            <flux:input
                wire:model="password"
                type="password"
                label="Password"
                placeholder="{{ $editingUserId ? 'Kosongkan jika tidak ingin diubah' : 'Minimal 6 karakter' }}"
                viewable
                :required="!$editingUserId" />

            <div class="p-3 bg-slate-50 dark:bg-zinc-800 rounded-lg border border-slate-200 dark:border-zinc-700">
                <flux:checkbox
                    wire:model="is_it_staff"
                    label="Berikan Akses Staf IT"
                    description="User ini akan bisa melihat dashboard monitor tiket dan melakukan pengerjaan IT." />
            </div>

            <div class="flex gap-2 justify-end pt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $editingUserId ? 'Simpan Perubahan' : 'Simpan User' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>