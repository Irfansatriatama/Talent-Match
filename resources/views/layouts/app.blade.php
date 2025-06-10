{{-- File resources/views/layouts/app.blade.php atau nama file layout utama Anda --}}
<x-layouts.base>
    @if (in_array(request()->route()->getName(),['static-sign-in', 'static-sign-up', 'register', 'login','password.forgot','reset-password']))
        {{-- Layout untuk halaman otentikasi --}}
        <div class="container position-sticky z-index-sticky top-0">
            <div class="row">
                <div class="col-12">
                    </div>
            </div>
        </div>
        @if (in_array(request()->route()->getName(),['static-sign-in', 'login','password.forgot','reset-password']))
        <main class="main-content mt-0">
            <div class="page-header page-header-bg align-items-start min-vh-100">
                <span class="mask bg-gradient-dark opacity-6"></span>
                {{ $slot }}
                </div>
        </main>
        @else
        {{ $slot }}
        @endif

    @else
        {{-- Layout untuk pengguna yang sudah login --}}
        @auth {{-- Pastikan pengguna sudah login sebelum memeriksa peran --}}
            @if (auth()->user()->role == 'hr')
                <x-navbars.sidebar-hr /> {{-- Tampilkan sidebar HR --}}
            @elseif (auth()->user()->role == 'candidate')
                <x-navbars.sidebar /> {{-- Tampilkan sidebar Candidate --}}
            @else
                {{-- Fallback jika peran tidak diketahui atau untuk peran lain --}}
                <x-navbars.sidebar /> {{-- Sidebar default jika ada --}}
            @endif
        @endauth

        <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
            <x-navbars.navs.auth :pageTitle="$pageTitle ?? 'Page'" />
            
            <div class="container-fluid py-4">
                {{-- ▼▼▼ INI SOLUSI UTAMANYA ▼▼▼ --}}
                @if (isset($slot))
                    {{-- Jika layout ini dipanggil sebagai komponen <x-layouts.app> --}}
                    {{-- (misalnya oleh Livewire Full-page seperti Dashboard HR), --}}
                    {{-- maka $slot akan ada isinya. Tampilkan slot tersebut. --}}
                    {{ $slot }}
                @else
                    {{-- Jika layout ini dipanggil menggunakan @extends('layouts.app'), --}}
                    {{-- (misalnya oleh halaman dari Controller seperti show, network-definition), --}}
                    {{-- maka $slot tidak ada, dan kita gunakan @yield. --}}
                    @yield('content')
                @endif
                {{-- ▲▲▲ AKHIR DARI SOLUSI ▲▲▲ --}}
            </div>

            <x-footers.auth />
        </main>
    @endif
</x-layouts.base>