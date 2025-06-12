{{--
    File: resources/views/hr/anp-analysis/network-definition.blade.php
    Kegunaan: Halaman untuk membangun atau mengedit struktur jaringan ANP.
    Memuat komponen Livewire: <livewire:hr.anp.network-builder />
    Penting: Controller harus mengirimkan variabel $anpAnalysis ke view ini.
--}}

@extends('layouts.app')

@section('title', 'Definisi Jaringan ANP')

@section('content')
<div class="container-fluid">
    {{-- Judul Halaman --}}
    <h1 class="h3 mb-2 text-gray-800">Definisi Jaringan ANP</h1>
    <p class="mb-4">Definisikan elemen, cluster (jika perlu), dan interdependensi untuk analisis: <strong>{{ $anpAnalysis->name }}</strong>.</p>
    
    {{-- Kartu yang berisi komponen Livewire --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            {{--
                Komponen Livewire 'NetworkBuilder' akan menangani semua logika
                untuk menambah/menghapus elemen, cluster, dan dependensi.
                Controller harus passing variabel $anpAnalysis.
            --}}
            @livewire('hr.anp.network-builder', ['anpAnalysis' => $anpAnalysis])
        </div>
    </div>
</div>
@endsection
