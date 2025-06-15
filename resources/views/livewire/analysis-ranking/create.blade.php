@extends('layouts.app')

@section('title', 'Buat Analisis ANP Baru')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Buat Analisis ANP Baru</h1>
    <p class="mb-4">Mulai sesi analisis baru dengan memilih posisi jabatan dan kandidat yang akan dievaluasi.</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Langkah 1: Inisiasi Analisis</h6>
        </div>
        <div class="card-body">
            @livewire('HR.anp.create-analysis-form')
        </div>
    </div>
</div>
@endsection
