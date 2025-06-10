{{--
    File: resources/views/hr/anp-analysis/create.blade.php
    Kegunaan: Halaman untuk memulai sesi analisis ANP baru.
    Memuat komponen Livewire: <livewire:hr.anp.create-analysis-form />
--}}

@extends('layouts.app')

@section('title', 'Buat Analisis ANP Baru')

@section('content')
<div class="container-fluid">
    {{-- Judul Halaman --}}
    <h1 class="h3 mb-2 text-gray-800">Buat Analisis ANP Baru</h1>
    <p class="mb-4">Mulai sesi analisis baru dengan memilih posisi jabatan dan kandidat yang akan dievaluasi.</p>

    {{-- Kartu yang berisi komponen Livewire --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Langkah 1: Inisiasi Analisis</h6>
        </div>
        <div class="card-body">
            {{--
                Semua logika form (input nama, pemilihan posisi & kandidat, validasi,
                dan penyimpanan) dihandle oleh komponen Livewire 'CreateAnalysisForm'.
            --}}
            @livewire('hr.anp.create-analysis-form')
        </div>
    </div>
</div>
@endsection
