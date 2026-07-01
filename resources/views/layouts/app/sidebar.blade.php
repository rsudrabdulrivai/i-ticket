<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 antialiased">
    @if(!request()->routeIs('public.monitor'))
    <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            {{-- Gunakan route home/monitor jika tidak login --}}
            <x-app-logo :sidebar="true" href="{{ auth()->check() ? route('dashboard') : route('public.monitor') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Menu')" class="grid">
                {{-- Menu yang HANYA tampil jika sudah LOGIN --}}
                @auth
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                {{-- Cek apakah user adalah staff IT --}}
                @if(auth()->user()->is_it_staff)
                <flux:sidebar.item icon="computer-desktop" :href="route('it-monitor')" :current="request()->routeIs('it-monitor')" wire:navigate>
                    {{ __('Monitor Tiket') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="user-group" :href="route('it-staff')" :current="request()->routeIs('it-staff')" wire:navigate>
                    {{ __('Manajemen Pengguna') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="server-stack" :href="route('inventory')" :current="request()->routeIs('inventory')" wire:navigate>
                    {{ __('Inventaris') }}
                </flux:sidebar.item>
                 {{-- Menu yang BOLEH dilihat tamu (Public) --}}
                <flux:sidebar.item icon="chart-bar" :href="route('public.monitor')" :current="request()->routeIs('public.monitor')" wire:navigate>
                    {{ __('Public Monitor') }}
                </flux:sidebar.item>
                <flux:sidebar.item 
                    icon="book-open" 
                    href="{{ route('knowledge.index') }}" 
                    :current="request()->routeIs('knowledge.index')">
                    Bank Knowledge
                </flux:sidebar.item>
                @endif
                @endauth
            </flux:sidebar.group>
        </flux:sidebar.nav>

        {{-- Ganti bagian bawah sidebar yang lama --}}
        <flux:spacer />

        @auth
        {{-- Komponen ini hanya dipanggil jika sudah LOGIN --}}
        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        @else
        {{-- Tampilan untuk Tamu/Publik --}}
        <div class="px-4 py-3">
            <flux:button
                icon="arrow-right-start-on-rectangle"
                href="{{ route('login') }}"
                variant="subtle"
                size="sm"
                class="w-full justify-start text-zinc-500"
                wire:navigate>
                {{ __('Login Staff') }}
            </flux:button>
        </div>
        @endauth
    </flux:sidebar>
    @endif


    @if(!request()->routeIs('public.monitor'))
    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        @auth
        <flux:dropdown position="top" align="end">
            <flux:profile
                :initials="auth()->user()->initials()"
                icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar
                                :name="auth()->user()->name"
                                :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer"
                        data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
        @else
        <flux:button
            icon="arrow-right-start-on-rectangle"
            href="{{ route('login') }}"
            variant="subtle"
            size="sm"
            wire:navigate>
            {{ __('Login') }}
        </flux:button>
        @endauth
    </flux:header>
    @endif

    {{ $slot }}

    @fluxScripts
</body>

</html>