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
                            <div class="mt-4">
                                <h6 class="mb-3">
                                    <i class="material-icons text-sm align-middle">description</i>
                                    Deskripsi Analisis
                                </h6>
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <p class="text-sm mb-0">{{ $anpAnalysis->description ?: 'Tidak ada deskripsi.' }}</p>
                                    </div>
                                </div>
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
                                                    {{-- KUNCI PERBAIKAN: Periksa keberadaan 'element_ids' sebelum melakukan loop --}}
                                                    @if(isset($comparison->comparison_data['matrix_values']) && is_array($comparison->comparison_data['matrix_values']) && isset($comparison->comparison_data['element_ids']))
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">Kriteria</th>
                                                                        @foreach($comparison->comparison_data['element_ids'] as $colId)
                                                                            @php
                                                                                $colElement = ($comparison->compared_elements_type == \App\Models\AnpCluster::class)
                                                                                    ? $anpAnalysis->networkStructure->clusters->find($colId) 
                                                                                    : $anpAnalysis->networkStructure->elements->find($colId);
                                                                            @endphp
                                                                            <th class="text-center">{{ $colElement->name ?? 'ID:'.$colId }}</th>
                                                                        @endforeach
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($comparison->comparison_data['element_ids'] as $rowId)
                                                                        @php
                                                                            $rowElement = ($comparison->compared_elements_type == \App\Models\AnpCluster::class)
                                                                                ? $anpAnalysis->networkStructure->clusters->find($rowId) 
                                                                                : $anpAnalysis->networkStructure->elements->find($rowId);
                                                                        @endphp
                                                                        <tr>
                                                                            <td class="font-weight-bold">{{ $rowElement->name ?? 'ID:'.$rowId }}</td>
                                                                            @foreach($comparison->comparison_data['element_ids'] as $colId)
                                                                                <td class="text-center">
                                                                                    {{ number_format($comparison->comparison_data['matrix_values'][$rowId][$colId] ?? 0, 3) }}
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
                                                            <p class="text-muted text-sm mt-2 mb-0">Data matriks perbandingan kriteria tidak lengkap.</p>
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
                                                        {{-- KUNCI PERBAIKAN: Logika berbeda, ID diambil dari key matriks, bukan 'element_ids' --}}
                                                        @if(isset($comparison->comparison_data['matrix_values']) && is_array($comparison->comparison_data['matrix_values']))
                                                            @php
                                                                $elementIds = array_keys($comparison->comparison_data['matrix_values']);
                                                            @endphp
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="text-center">Elemen</th>
                                                                            @foreach($elementIds as $colId)
                                                                                <th class="text-center">{{ $anpAnalysis->networkStructure->elements->find($colId)->name ?? 'ID:'.$colId }}</th>
                                                                            @endforeach
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($elementIds as $rowId)
                                                                            <tr>
                                                                                <td class="font-weight-bold">{{ $anpAnalysis->networkStructure->elements->find($rowId)->name ?? 'ID:'.$rowId }}</td>
                                                                                @foreach($elementIds as $colId)
                                                                                    <td class="text-center">
                                                                                        {{ number_format($comparison->comparison_data['matrix_values'][$rowId][$colId] ?? 0, 3) }}
                                                                                    </td>
                                                                                @endforeach
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="text-center py-3">
                                                                <p class="text-muted text-sm mt-2 mb-0">Data matriks interdependensi tidak lengkap.</p>
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
                                                    {{-- KUNCI PERBAIKAN: Logika berbeda, ID diambil dari key matriks, nama dari relasi 'candidates' --}}
                                                    @if(isset($comparison->comparison_data['matrix_values']) && is_array($comparison->comparison_data['matrix_values']))
                                                        @php
                                                            $candidateIds = array_keys($comparison->comparison_data['matrix_values']);
                                                        @endphp
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center">Kandidat</th>
                                                                        @foreach($candidateIds as $colId)
                                                                            <th class="text-center">{{ $anpAnalysis->candidates->find($colId)->name ?? 'ID:'.$colId }}</th>
                                                                        @endforeach
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($candidateIds as $rowId)
                                                                        <tr>
                                                                            <td class="font-weight-bold">{{ $anpAnalysis->candidates->find($rowId)->name ?? 'ID:'.$rowId }}</td>
                                                                            @foreach($candidateIds as $colId)
                                                                                <td class="text-center">
                                                                                    {{ number_format($comparison->comparison_data['matrix_values'][$rowId][$colId] ?? 0, 3) }}
                                                                                </td>
                                                                            @endforeach
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="text-center py-3">
                                                            <p class="text-muted text-sm mt-2 mb-0">Data matriks alternatif tidak lengkap.</p>
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