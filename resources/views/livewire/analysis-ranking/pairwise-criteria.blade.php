@extends('layouts.app')

@section('title', 'Perbandingan Kriteria/Cluster')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Perbandingan Kriteria/Cluster</h1>
    <p class="mb-4">Lakukan perbandingan berpasangan untuk menentukan bobot relatif kriteria atau cluster.</p>

    <div class="card shadow mb-4">
        <div class="card-body">
            @livewire('h-r.anp.pairwise-criteria-matrix', [
                'anpAnalysis' => $anpAnalysis,
                'controlCriterionContextType' => $controlCriterionContextType,
                'controlCriterionContextId' => $controlCriterionContextId
            ])
        </div>
    </div>
</div>
@endsection
