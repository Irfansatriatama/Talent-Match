<x-layouts.base>
    @if (in_array(request()->route()->getName(),['static-sign-in', 'static-sign-up', 'register', 'login','password.forgot','reset-password']))
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
        @auth 
            @if (auth()->user()->role == 'HR')
                <x-navbars.sidebar-hr /> 
            @elseif (auth()->user()->role == 'candidate')
                <x-navbars.sidebar /> 
            @else
                <x-navbars.sidebar /> 
            @endif
        @endauth

        <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
            <x-navbars.navs.auth :pageTitle="$pageTitle ?? 'Page'" />
            
            <div class="container-fluid py-4">
                @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </div>

            <x-footers.auth />
        </main>
    @endif
</x-layouts.base>