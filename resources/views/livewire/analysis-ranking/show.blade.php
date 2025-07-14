@extends('layouts.app')

@section('title', 'Hasil Analisis ANP')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header p-3">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-0">Hasil Analisis: {{ $anpAnalysis->name }}</h5>
                            <p class="text-sm mb-0">Posisi: {{ $anpAnalysis->jobPosition->name }}</p>
                            <p class="text-xs text-muted mb-0">
                                Status: 
                                <span class="badge badge-sm bg-gradient-{{ $anpAnalysis->status == 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($anpAnalysis->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('h-r.anp.analysis.index') }}" class="btn btn-sm btn-secondary">
                                <i class="material-icons text-sm">arrow_back</i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-3">
                    <!-- Stepper -->
                    <x-anp-stepper currentStep="4" />
                    
                    <!-- Tabel Peringkat -->
                    <div class="mb-4">
                        <h6 class="mb-3">
                            <i class="material-icons text-sm align-middle">emoji_events</i> 
                            Peringkat Kandidat
                        </h6>
                        
                        @if($anpAnalysis->results->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr class="text-center">
                                            <th style="width: 10%;">Peringkat</th>
                                            <th>Nama Kandidat</th>
                                            <th style="width: 20%;">Skor Global</th>
                                            <th style="width: 15%;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($anpAnalysis->results->sortBy('rank') as $result)
                                            <tr>
                                                <td class="text-center font-weight-bold align-middle">
                                                    <span class="badge 
                                                        @if($result->rank == 1) bg-gradient-success
                                                        @elseif($result->rank == 2) bg-gradient-info
                                                        @elseif($result->rank == 3) bg-gradient-warning
                                                        @else bg-secondary @endif">
                                                        #{{ $result->rank }}
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <div class="avatar avatar-sm rounded-circle bg-gradient-primary">
                                                                <span class="text-white text-xs">
                                                                    {{ substr($result->candidate->name, 0, 1) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-sm">{{ $result->candidate->name }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge bg-gradient-success fs-6">
                                                        {{ number_format($result->score, 4) }}
                                                    </span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <a href="{{ route('h-r.detail-candidate', ['candidate' => $result->candidate->id]) }}" 
                                                       class="btn btn-sm btn-info mb-0">
                                                        <i class="material-icons text-sm">visibility</i>
                                                        Lihat Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning text-white">
                                <i class="material-icons text-sm align-middle">info</i>
                                Belum ada hasil perhitungan. Silakan jalankan kalkulasi terlebih dahulu.
                            </div>
                        @endif
                    </div>

                    <!-- Description -->
                    @if($anpAnalysis->description)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="material-icons text-sm align-middle">description</i>
                                    Deskripsi Analisis
                                </h6>
                                <p class="text-sm mb-0">{{ $anpAnalysis->description }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Accordion Riwayat Detail Analisis -->
                    <div class="accordion accordion-flush" id="accordionRiwayat">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingRiwayat">
                                <button class="accordion-button collapsed bg-gradient-light" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapseRiwayat" 
                                        aria-expanded="false" aria-controls="collapseRiwayat">
                                    <i class="material-icons text-sm ms-3 me-2">history</i> 
                                    <strong>Riwayat Detail Analisis</strong>
                                </button>
                            </h2>
                            <div id="collapseRiwayat" class="accordion-collapse collapse" 
                                 aria-labelledby="headingRiwayat" data-bs-parent="#accordionRiwayat">
                                <div class="accordion-body bg-light p-4">
                                    
                                    <!-- 1. Struktur Jaringan -->
                                    <div class="mb-5">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="icon icon-sm icon-shape bg-gradient-primary shadow text-center border-radius-md me-3">
                                                <i class="material-icons text-white opacity-10">account_tree</i>
                                            </div>
                                            <h5 class="mb-0 text-dark">1. Struktur Jaringan ANP</h5>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="card border-0 shadow-sm h-100">
                                                    <div class="card-header bg-gradient-light border-0 pb-0">
                                                        <h6 class="text-dark mb-0">
                                                            <i class="material-icons text-sm align-middle me-1">layers</i>
                                                            Cluster & Elemen
                                                        </h6>
                                                    </div>
                                                    <div class="card-body pt-3">
                                                        @forelse($anpAnalysis->networkStructure->clusters as $cluster)
                                                            <div class="mb-3 pb-3 border-bottom">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <span class="badge bg-gradient-primary me-2">
                                                                        {{ $cluster->name }}
                                                                    </span>
                                                                    <small class="text-muted">
                                                                        ({{ $cluster->elements->count() }} elemen)
                                                                    </small>
                                                                </div>
                                                                <div class="ms-3">
                                                                    @forelse($cluster->elements as $element)
                                                                        <div class="d-flex align-items-center mb-1">
                                                                            <i class="material-icons text-sm text-secondary me-2">fiber_manual_record</i>
                                                                            <span class="text-sm">{{ $element->name }}</span>
                                                                        </div>
                                                                    @empty
                                                                        <span class="text-muted text-sm">Tidak ada elemen</span>
                                                                    @endforelse
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="text-center py-3">
                                                                <i class="material-icons text-secondary opacity-6" style="font-size: 3rem;">layers_clear</i>
                                                                <p class="text-muted text-sm mt-2 mb-0">Tidak ada cluster</p>
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="card border-0 shadow-sm h-100">
                                                    <div class="card-header bg-gradient-light border-0 pb-0">
                                                        <h6 class="text-dark mb-0">
                                                            <i class="material-icons text-sm align-middle me-1">sync_alt</i>
                                                            Interdependensi
                                                        </h6>
                                                    </div>
                                                    <div class="card-body pt-3">
                                                        @forelse($anpAnalysis->networkStructure->dependencies as $dep)
                                                            <div class="mb-3 p-3 bg-light rounded">
                                                                <div class="row align-items-center">
                                                                    <div class="col-12 col-md-5 mb-2 mb-md-0">
                                                                        <span class="badge bg-gradient-info text-xs d-inline-block" 
                                                                            style="max-width: 100%; word-wrap: break-word; white-space: normal;">
                                                                            {{ $dep->sourceable->name }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="col-12 col-md-2 text-center mb-2 mb-md-0">
                                                                        <i class="material-icons text-sm text-secondary">arrow_forward</i>
                                                                    </div>
                                                                    <div class="col-12 col-md-5">
                                                                        <span class="badge bg-gradient-warning text-xs d-inline-block" 
                                                                            style="max-width: 100%; word-wrap: break-word; white-space: normal;">
                                                                            {{ $dep->targetable->name }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="text-center py-3">
                                                                <i class="material-icons text-secondary opacity-6" style="font-size: 3rem;">sync_disabled</i>
                                                                <p class="text-muted text-sm mt-2 mb-0">Tidak ada interdependensi</p>
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Perbandingan Kriteria -->
                                    <div class="mb-5">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="icon icon-sm icon-shape bg-gradient-success shadow text-center border-radius-md me-3">
                                                <i class="material-icons text-white opacity-10">compare_arrows</i>
                                            </div>
                                            <h5 class="mb-0 text-dark">2. Perbandingan Kriteria</h5>
                                        </div>
                                        
                                        @forelse($anpAnalysis->criteriaComparisons as $index => $comparison)
                                            <div class="card border-0 shadow-sm mb-3">
                                                <div class="card-header bg-gradient-light border-0 d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1 text-dark">Matriks Perbandingan #{{ $index + 1 }}</h6>
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-sm text-muted me-2">Konteks:</span>
                                                            @if($comparison->control_criterionable_type == 'goal')
                                                                <span class="badge bg-gradient-dark">Goal (Tujuan Utama)</span>
                                                            @else
                                                                <span class="badge bg-gradient-success">
                                                                    {{ $comparison->controlCriterionable->name ?? 'N/A' }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    @if($comparison->consistency)
                                                        <div class="text-end">
                                                            <div class="text-xs text-muted mb-1">Consistency Ratio</div>
                                                            <div class="d-flex align-items-center">
                                                                <span class="text-sm fw-bold me-2">
                                                                    {{ number_format($comparison->consistency->consistency_ratio, 4) }}
                                                                </span>
                                                                <span class="badge {{ $comparison->consistency->is_consistent ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                                    {{ $comparison->consistency->is_consistent ? 'Konsisten' : 'Tidak Konsisten' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="card-body pt-3">
                                                    @if($comparison->comparison_matrix && is_array($comparison->comparison_matrix))
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-hover mb-0">
                                                                <thead class="bg-gradient-dark">
                                                                    <tr>
                                                                        <th class="text-center text-white text-xs">Kriteria</th>
                                                                        @foreach(array_keys(reset($comparison->comparison_matrix)) as $header)
                                                                            <th class="text-center text-white text-xs">{{ $header }}</th>
                                                                        @endforeach
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($comparison->comparison_matrix as $row => $values)
                                                                        <tr>
                                                                            <th class="text-xs bg-light">{{ $row }}</th>
                                                                            @foreach($values as $value)
                                                                                <td class="text-center text-xs">
                                                                                    <span class="badge bg-gradient-secondary">
                                                                                        {{ number_format($value, 3) }}
                                                                                    </span>
                                                                                </td>
                                                                            @endforeach
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="text-center py-3">
                                                            <i class="material-icons text-secondary opacity-6" style="font-size: 3rem;">table_chart</i>
                                                            <p class="text-muted text-sm mt-2 mb-0">Data matriks tidak tersedia</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4">
                                                <i class="material-icons text-secondary opacity-6" style="font-size: 4rem;">compare_arrows</i>
                                                <p class="text-muted mt-3 mb-0">Tidak ada data perbandingan kriteria</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    <!-- 3. Perbandingan Interdependensi -->
                                    @if($anpAnalysis->interdependencyComparisons->count() > 0)
                                        <div class="mb-5">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="icon icon-sm icon-shape bg-gradient-info shadow text-center border-radius-md me-3">
                                                    <i class="material-icons text-white opacity-10">sync_alt</i>
                                                </div>
                                                <h5 class="mb-0 text-dark">3. Perbandingan Interdependensi</h5>
                                            </div>
                                            
                                            @foreach($anpAnalysis->interdependencyComparisons as $index => $comparison)
                                                <div class="card border-0 shadow-sm mb-3">
                                                    <div class="card-header bg-gradient-light border-0 d-flex align-items-center justify-content-between">
                                                        <div>
                                                            <h6 class="mb-1 text-dark">Matriks Interdependensi #{{ $index + 1 }}</h6>
                                                            <div class="d-flex align-items-center">
                                                                <span class="text-sm text-muted me-2">Dependensi:</span>
                                                                <span class="badge bg-gradient-info me-1">
                                                                    {{ $comparison->dependency->sourceable->name ?? 'N/A' }}
                                                                </span>
                                                                <i class="material-icons text-sm text-secondary mx-2">arrow_forward</i>
                                                                <span class="badge bg-gradient-warning">
                                                                    {{ $comparison->dependency->targetable->name ?? 'N/A' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        @if($comparison->consistency)
                                                            <div class="text-end">
                                                                <div class="text-xs text-muted mb-1">Consistency Ratio</div>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="text-sm fw-bold me-2">
                                                                        {{ number_format($comparison->consistency->consistency_ratio, 4) }}
                                                                    </span>
                                                                    <span class="badge {{ $comparison->consistency->is_consistent ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                                        {{ $comparison->consistency->is_consistent ? 'Konsisten' : 'Tidak Konsisten' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="card-body pt-3">
                                                        @if($comparison->comparison_matrix && is_array($comparison->comparison_matrix))
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-hover mb-0">
                                                                    <thead class="bg-gradient-dark">
                                                                        <tr>
                                                                            <th class="text-center text-white text-xs">Elemen</th>
                                                                            @foreach(array_keys(reset($comparison->comparison_matrix)) as $header)
                                                                                <th class="text-center text-white text-xs">{{ $header }}</th>
                                                                            @endforeach
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($comparison->comparison_matrix as $row => $values)
                                                                            <tr>
                                                                                <th class="text-xs bg-light">{{ $row }}</th>
                                                                                @foreach($values as $value)
                                                                                    <td class="text-center text-xs">
                                                                                        <span class="badge bg-gradient-secondary">
                                                                                            {{ number_format($value, 3) }}
                                                                                        </span>
                                                                                    </td>
                                                                                @endforeach
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="text-center py-3">
                                                                <i class="material-icons text-secondary opacity-6" style="font-size: 3rem;">table_chart</i>
                                                                <p class="text-muted text-sm mt-2 mb-0">Data matriks tidak tersedia</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- 4. Perbandingan Alternatif -->
                                    <div class="mb-2">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="icon icon-sm icon-shape bg-gradient-warning shadow text-center border-radius-md me-3">
                                                <i class="material-icons text-white opacity-10">people</i>
                                            </div>
                                            <h5 class="mb-0 text-dark">4. Perbandingan Alternatif (Kandidat)</h5>
                                        </div>
                                        
                                        @forelse($anpAnalysis->alternativeComparisons as $index => $comparison)
                                            <div class="card border-0 shadow-sm mb-3">
                                                <div class="card-header bg-gradient-light border-0 d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1 text-dark">Matriks Kandidat #{{ $index + 1 }}</h6>
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-sm text-muted me-2">Berdasarkan Kriteria:</span>
                                                            <span class="badge bg-gradient-warning">
                                                                {{ $comparison->element->name ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    @if($comparison->consistency)
                                                        <div class="text-end">
                                                            <div class="text-xs text-muted mb-1">Consistency Ratio</div>
                                                            <div class="d-flex align-items-center">
                                                                <span class="text-sm fw-bold me-2">
                                                                    {{ number_format($comparison->consistency->consistency_ratio, 4) }}
                                                                </span>
                                                                <span class="badge {{ $comparison->consistency->is_consistent ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                                    {{ $comparison->consistency->is_consistent ? 'Konsisten' : 'Tidak Konsisten' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="card-body pt-3">
                                                    @if($comparison->comparison_matrix && is_array($comparison->comparison_matrix))
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-hover mb-0">
                                                                <thead class="bg-gradient-dark">
                                                                    <tr>
                                                                        <th class="text-center text-white text-xs">Kandidat</th>
                                                                        @foreach(array_keys(reset($comparison->comparison_matrix)) as $header)
                                                                            <th class="text-center text-white text-xs">{{ $header }}</th>
                                                                        @endforeach
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($comparison->comparison_matrix as $row => $values)
                                                                        <tr>
                                                                            <th class="text-xs bg-light">{{ $row }}</th>
                                                                            @foreach($values as $value)
                                                                                <td class="text-center text-xs">
                                                                                    <span class="badge bg-gradient-secondary">
                                                                                        {{ number_format($value, 3) }}
                                                                                    </span>
                                                                                </td>
                                                                            @endforeach
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="text-center py-3">
                                                            <i class="material-icons text-secondary opacity-6" style="font-size: 3rem;">table_chart</i>
                                                            <p class="text-muted text-sm mt-2 mb-0">Data matriks tidak tersedia</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4">
                                                <i class="material-icons text-secondary opacity-6" style="font-size: 4rem;">people</i>
                                                <p class="text-muted mt-3 mb-0">Tidak ada data perbandingan alternatif</p>
                                            </div>
                                        @endforelse
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection