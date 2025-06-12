<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h5 class="mb-0">Perbandingan Alternatif (Kandidat)</h5>
            <p class="text-sm">Bandingkan setiap kandidat berdasarkan kriteria yang dipilih.</p>
        </div>
        <div class="card-body">
            {{-- IMPLEMENTASI KOMPONEN STEPPER --}}
            <x-anp-stepper currentStep="3" />

            {{-- KONTEKS PERBANDINGAN --}}
            <div class="alert alert-light text-dark p-3 text-center mb-4" role="alert">
                <h6 class="text-dark mb-1">Konteks Kriteria</h6>
                <p class="mb-0">
                    Membandingkan kandidat berdasarkan kriteria: 
                    <strong class="text-primary">{{ $criterionElement->name }}</strong>
                </p>
            </div>

            @if (count($alternativesToCompare) < 2)
                <div class="alert alert-warning text-white">
                    Minimal 2 kandidat diperlukan untuk perbandingan.
                </div>
            @else
                {{-- TOMBOL ISI OTOMATIS --}}
                <div class="d-flex justify-content-end mb-3">
                    <button wire:click="autoFillMatrix" class="btn btn-sm btn-outline-secondary mb-0">
                        <i class="material-icons text-sm">auto_fix_high</i> Isi Otomatis dari Skor Tes
                    </button>
                </div>

                {{-- MATRIKS PERBANDINGAN --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered text-center align-items-center">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kandidat</th>
                                @foreach ($alternativesToCompare as $colCand)
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="white-space: normal; vertical-align: middle;">
                                        {{ $colCand->name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($alternativesToCompare as $rowIndex => $rowCand)
                                <tr wire:key="alt-row-{{ $rowCand->id }}">
                                    <td class="text-start text-sm font-weight-bold align-middle">
                                        {{ $rowCand->name }}
                                    </td>
                                    @foreach ($alternativesToCompare as $colIndex => $colCand)
                                        <td class="p-1 align-middle" wire:key="alt-cell-{{ $rowCand->id }}-{{ $colCand->id }}">
                                            <div class="input-group input-group-outline">
                                                @if ($rowCand->id == $colCand->id)
                                                    <input type="text" class="form-control form-control-sm text-center" value="1" readonly disabled>
                                                @else
                                                    <input type="number" step="any" min="0.11" max="9"
                                                        wire:model.live="matrixValues.{{ $rowCand->id }}.{{ $colCand->id }}"
                                                        class="form-control form-control-sm text-center @error('matrixValues.'.$rowCand->id.'.'.$colCand->id) is-invalid @enderror"
                                                        @if ($rowIndex > $colIndex) readonly style="background-color: #f0f2f5; color: #6c757d;" @endif
                                                    >
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @foreach ($errors->get('matrixValues.*.*') as $message)
                        <div class="text-danger text-xs ps-1">{{ $message }}</div>
                    @endforeach
                </div>

                {{-- AREA BAWAH: 2 KOLOM --}}
                <div class="row">
                    {{-- KOLOM KIRI: PANDUAN SKALA SAATY --}}
                    <div class="col-lg-5">
                        <x-saaty-scale-guide />
                    </div>

                    {{-- KOLOM KANAN: HASIL KALKULASI & AKSI --}}
                    <div class="col-lg-7">
                        <div class="card bg-gray-100">
                            <div class="card-header pb-2">
                                <h6 class="mb-0">Hasil Kalkulasi & Aksi</h6>
                            </div>
                            <div class="card-body">
                                @if ($calculationResult && !isset($calculationResult['error']))
                                    <div class="row">
                                        <div class="col-md-4 text-center border-end">
                                            <h6 class="text-xs text-uppercase mb-1">Rasio Konsistensi</h6>
                                            <h3 class="font-weight-bolder @if(!$isConsistent) text-danger @else text-success @endif mb-0">
                                                {{ number_format($consistencyRatio, 4) }}
                                            </h3>
                                            @if ($isConsistent)
                                                <span class="badge bg-gradient-success">Konsisten</span>
                                            @else
                                                <span class="badge bg-gradient-danger">Tidak Konsisten</span>
                                            @endif
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="text-xs text-uppercase mb-2">Vektor Prioritas Kandidat</h6>
                                            <div class="table-responsive" style="max-height: 150px;">
                                                <table class="table table-sm align-items-center mb-0">
                                                    @foreach ($priorityVector as $candidateId => $weight)
                                                        <tr>
                                                            <td class="text-sm">{{ collect($alternativesToCompare)->firstWhere('id', $candidateId)->name ?? 'N/A' }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-gradient-info">{{ number_format($weight, 4) }}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    @if (!$isConsistent)
                                        <div class="alert alert-danger text-white mt-3 mb-0" role="alert">
                                            <small>CR harus ≤ {{ config('anp.consistency_ratio_threshold', 0.10) }}. Harap perbaiki nilai perbandingan.</small>
                                        </div>
                                    @endif
                                @elseif(isset($calculationResult['error']))
                                    <div class="alert alert-danger text-white" role="alert">
                                        {{ $calculationResult['error'] }}
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="material-icons text-5xl text-secondary opacity-5">calculate</i>
                                        <p class="text-sm text-secondary mt-2">Tekan tombol "Hitung Konsistensi" untuk melihat hasilnya</p>
                                    </div>
                                @endif

                                <div class="d-grid gap-2 mt-3">
                                    <button wire:click="recalculateConsistency" class="btn btn-outline-info" wire:loading.attr="disabled">
                                        <span wire:loading.remove>Hitung Konsistensi</span>
                                        <span wire:loading>Menghitung...</span>
                                    </button>
                                    <button wire:click="calculateAndFinish" class="btn bg-gradient-primary" 
                                        wire:loading.attr="disabled" 
                                        {{ $isConsistent === true ? '' : 'disabled' }}>
                                        Simpan & Lanjutkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <hr class="horizontal dark my-4">
            <div class="d-flex justify-content-between">
                <a href="{{ route('hr.anp.analysis.network.define', ['anpAnalysis' => $analysis->id]) }}" class="btn btn-outline-secondary">
                    <i class="material-icons text-sm">arrow_back</i> Kembali ke Definisi Jaringan
                </a>
            </div>
        </div>
    </div>
</div>