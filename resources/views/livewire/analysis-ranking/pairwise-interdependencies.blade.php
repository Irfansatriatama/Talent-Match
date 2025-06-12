{{--
    File: resources/views/hr/anp-analysis/pairwise-criteria.blade.php
    Kegunaan: Halaman untuk perbandingan berpasangan kriteria/cluster.
    Memuat komponen Livewire: <livewire:hr.anp.pairwise-criteria-matrix />
    Penting: Controller harus mengirimkan variabel $anpAnalysis, $controlCriterionContextType, $controlCriterionContextId.
--}}

@extends('layouts.app')

@section('title', 'Perbandingan Kriteria/Cluster')

@section('content')
<div class="container-fluid">
    {{-- Judul Halaman --}}
    <h1 class="h3 mb-2 text-gray-800">Perbandingan Kriteria/Cluster</h1>
    <p class="mb-4">Lakukan perbandingan berpasangan untuk menentukan bobot relatif kriteria atau cluster.</p>

    {{-- Kartu yang berisi komponen Livewire --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            {{--
                Komponen Livewire 'PairwiseCriteriaMatrix' akan menampilkan matriks perbandingan
                berdasarkan konteks (control criterion) yang diberikan oleh controller.
            --}}
            {{-- INI PANGGILAN YANG BENAR --}}
            @livewire('hr.anp.pairwise-interdependencies-matrix', [
                'anpAnalysis' => $anpAnalysis,
                'anpDependency' => $anpDependency
            ])
    </div>
</div>
@endsection
