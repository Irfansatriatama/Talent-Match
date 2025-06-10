{{-- File: resources/views/layouts/test-taker.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Talent Match') }} - Assessment</title>
    <link rel="icon" type="image/png" href="{{ asset('assets') }}/img/ic-talent-match.png">
    <link id="pagestyle" href="{{ asset('assets') }}/css/material-dashboard.css?v=3.0.0" rel="stylesheet" />
    @livewireStyles
    <style>
        body {
            background-color: #f8f9fa; 
        }
        .test-container {
            max-width: 800px; 
            margin-top: 3rem;
            margin-bottom: 3rem;
        }
        
    </style>
</head>
<body class="bg-light">
    <main class="main-content position-relative max-height-vh-100 h-100">
        <div class="container-fluid py-4 test-container">
            {{ $slot }} {{-- Konten dari komponen Livewire tes akan dimuat di sini --}}
        </div>
    </main>

    <script src="{{ asset('assets') }}/js/core/popper.min.js"></script>
    <script src="{{ asset('assets') }}/js/core/bootstrap.min.js"></script>
    @livewireScripts
    @stack('scripts') {{-- Untuk script khusus per halaman tes --}}
</body>
</html>