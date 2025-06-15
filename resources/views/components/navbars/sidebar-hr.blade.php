<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 d-flex text-wrap align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets') }}/img/ic-talent-match.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-2 font-weight-bold text-white">TALENT MATCH</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center {{ Route::currentRouteName() == 'h-r.dashboard' ? ' active bg-gradient-primary' : '' }}"
                    href="{{ route('h-r.dashboard') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Pages</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center {{ str_contains(Route::currentRouteName(), 'h-r.anp.analysis') ? ' active bg-gradient-primary' : '' }}"
                    href="{{ route('h-r.anp.analysis.index') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">analytics</i>
                    </div>
                    <span class="nav-link-text ms-1">Analisis & Peringkat</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center {{ Route::currentRouteName() == 'h-r.candidates' ? ' active bg-gradient-primary' : '' }}"
                    href="{{ route('h-r.candidates') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">people</i> 
                    </div>
                    <span class="nav-link-text ms-1">Candidate</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center {{ Route::currentRouteName() == 'h-r.settings' ? ' active bg-gradient-primary' : '' }}"
                    href="{{ route('h-r.settings') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">settings</i> 
                    </div>
                    <span class="nav-link-text ms-1">Setting</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center" href="javascript:void(0);"
                   onclick="
                       event.preventDefault();
                       let livewireComponentRoot = this.querySelector('[wire\\:id]');
                       if (livewireComponentRoot) {
                           let componentId = livewireComponentRoot.getAttribute('wire:id')
                           Livewire.find(componentId).call('destroy');
                       } else {
                           console.error('Komponen Livewire untuk logout tidak ditemukan.');
                       }
                   ">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">logout</i>
                    </div>
                    <span class="nav-link-text ms-1">
                        <livewire:auth.logout/>
                    </span>
                </a>
            </li>
        </ul>
    </div>
    
    <style>
        .sidenav .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            border-radius: 0.375rem;
        }
        
        .sidenav .navbar-nav .nav-link .material-icons {
            font-size: 1.2rem;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidenav .navbar-nav .nav-link.active {
            margin: 0 0.5rem;
        }
    </style>
</aside>