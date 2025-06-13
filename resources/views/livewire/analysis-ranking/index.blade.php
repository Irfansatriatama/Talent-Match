@extends('layouts.app')

@section('title', 'Daftar Analisis ANP')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Analisis & Peringkat (ANP)</h1>
    <p class="mb-4">Kelola semua sesi analisis Analytic Network Process untuk perankingan kandidat.</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Sesi Analisis</h6>
        </div>
        <div class="card-body">
            @livewire('hr.anp.analysis-list')
        </div>
    </div>
</div>
@endsection
