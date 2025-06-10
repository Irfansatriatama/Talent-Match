{{-- resources/views/components/navbars/navs/auth.blade.php --}}
@props(['pageTitle' => null, 'breadcrumbCurrentPage' => null])

<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
    navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                <li class="breadcrumb-item text-sm text-dark active text-capitalize" aria-current="page">
                    {{-- Gunakan variabel prop jika ada, jika tidak, gunakan nama route --}}
                    {{ $breadcrumbCurrentPage ?? ucfirst(str_replace(['-', '.'], ' ', Route::currentRouteName())) }}
                </li>
            </ol>
            <h6 class="font-weight-bolder mb-0 text-capitalize">
                {{-- Gunakan variabel prop jika ada, jika tidak, gunakan nama route --}}
                {{ $pageTitle ?? ucfirst(str_replace(['-', '.'], ' ', Route::currentRouteName())) }}
            </h6>
        </nav>
    </div>
</nav>