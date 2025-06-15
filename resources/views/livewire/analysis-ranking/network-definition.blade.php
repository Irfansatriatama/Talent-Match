@extends('layouts.app')

@section('title', 'Definisi Jaringan ANP')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Definisi Jaringan ANP</h1>
    <p class="mb-4">Definisikan elemen, cluster (jika perlu), dan interdependensi untuk analisis: <strong>{{ $anpAnalysis->name }}</strong>.</p>

    <div class="card shadow mb-4">
        <div class="card-body">
            @livewire('h-r.anp.network-builder', ['anpAnalysis' => $anpAnalysis])
        </div>
    </div>
</div>
@endsection
