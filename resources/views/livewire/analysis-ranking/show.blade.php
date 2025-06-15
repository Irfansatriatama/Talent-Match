@extends('layouts.app')

@section('title', 'Hasil Analisis ANP')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-2 text-gray-800">Hasil Akhir Analisis ANP</h1>
    <p class="mb-4">
        Peringkat kandidat untuk analisis <strong>"{{ $anpAnalysis->name }}"</strong> 
        posisi <strong>{{ $anpAnalysis->jobPosition->name }}</strong>.
    </p>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tabel Peringkat Kandidat</h6>
                    <a href="{{ route('HR.anp.analysis.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Analisis
                    </a>
                </div>
                <div class="card-body">
                    <x-anp-stepper currentStep="4" />

                    @if($anpAnalysis->results->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
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
                                            <td class="text-center font-weight-bold h5 align-middle">
                                                <span class="badge 
                                                    @if($result->rank == 1) bg-gradient-success
                                                    @elseif($result->rank == 2) bg-gradient-info
                                                    @elseif($result->rank == 3) bg-gradient-warning
                                                    @else bg-secondary @endif">
                                                    {{ $result->rank }}
                                                </span>
                                            </td>
                                            <td class="align-middle">{{ $result->candidate->name }}</td>
                                            <td class="text-center font-weight-bold align-middle">{{ number_format($result->score, 5) }}</td>
                                            
                                            <td class="text-center align-middle">
                                                <a href="{{ route('HR.detail-candidate', ['candidate' => $result->candidate->id]) }}" class="btn btn-sm btn-info mb-0">
                                                    Lihat Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            <h6>Deskripsi Analisis:</h6>
                            <p>{{ $anpAnalysis->description ?: 'Tidak ada deskripsi.' }}</p>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            Hasil perhitungan untuk analisis ini belum tersedia atau belum selesai. Silakan selesaikan semua langkah perbandingan dan jalankan kalkulasi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection