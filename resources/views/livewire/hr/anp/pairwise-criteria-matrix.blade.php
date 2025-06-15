<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h5 class="mb-0">Perbandingan Berpasangan Kriteria</h5>
            <p class="text-sm">Bandingkan tingkat kepentingan relatif antar item di bawah ini.</p>
        </div>
        <div class="card-body">
            {{-- IMPLEMENTASI KOMPONEN STEPPER --}}
            <x-anp-stepper currentStep="3" />

            {{-- KONTEKS PERBANDINGAN --}}
            <div class="alert alert-light text-dark p-3 text-center mb-4" role="alert">
                <h6 class="text-dark mb-1">Konteks Perbandingan</h6>
                <p class="mb-0">
                    Membandingkan {{ $elementTypeToCompare == App\Models\AnpElement::class ? 'Elemen' : 'Cluster' }} terhadap 
                    <strong class="text-primary">
                        @if ($controlCriterionContext === 'goal')
                            Goal Utama ({{ $analysis->jobPosition->name }})
                        @elseif ($controlCriterionObject)
                            {{ $controlCriterionObject->name }}
                        @endif
                    </strong>
                </p>
            </div>

            @if (count($elementsToCompare) < 2)
                <div class="alert alert-warning text-white">
                    Minimal 2 item diperlukan untuk perbandingan. Silakan kembali ke halaman Definisi Jaringan untuk menambahkan elemen/cluster.
                </div>
            @else
                {{-- MATRIKS PERBANDINGAN --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered text-center align-items-center">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Elemen</th>
                                @foreach ($elementsToCompare as $colElement)
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="white-space: normal; vertical-align: middle;">
                                        {{ $colElement->name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($elementsToCompare as $rowIndex => $rowElement)
                                <tr wire:key="row-{{ $rowElement->id }}">
                                    <td class="text-start text-sm font-weight-bold align-middle">
                                        {{ $rowElement->name }}
                                    </td>
                                    @foreach ($elementsToCompare as $colIndex => $colElement)
                                        <td class="p-1 align-middle" wire:key="cell-{{ $rowElement->id }}-{{ $colElement->id }}">
                                            <div class="input-group input-group-outline">
                                                @if ($rowElement->id == $colElement->id)
                                                    {{-- Diagonal cells are always 1 --}}
                                                    <input type="text" 
                                                        class="form-control form-control-sm text-center bg-light" 
                                                        value="1" 
                                                        readonly 
                                                        disabled>
                                                @else
                                                    {{-- All non-diagonal cells are editable --}}
                                                    <input type="number" 
                                                        step="any" 
                                                        min="0.11" 
                                                        max="9"
                                                        wire:model.live.debounce.500ms="matrixValues.{{ $rowElement->id }}.{{ $colElement->id }}"
                                                        class="form-control form-control-sm text-center @error('matrixValues.'.$rowElement->id.'.'.$colElement->id) is-invalid @enderror"
                                                        placeholder="{{ isset($matrixValues[$rowElement->id][$colElement->id]) ? '' : '?' }}">
                                                    @error('matrixValues.'.$rowElement->id.'.'.$colElement->id)
                                                        <div class="invalid-feedback text-xs">{{ $message }}</div>
                                                    @enderror
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- IMPLEMENTASI PANDUAN PENGISIAN MATRIKS --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <x-anp-matrix-guide />
                    </div>
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
                                            <h6 class="text-xs text-uppercase mb-2">Vektor Prioritas (Bobot)</h6>
                                            <div class="table-responsive" style="max-height: 150px;">
                                                <table class="table table-sm align-items-center mb-0">
                                                    @foreach ($priorityVector as $elementId => $weight)
                                                        <tr>
                                                            <td class="text-sm">{{ collect($elementsToCompare)->firstWhere('id', $elementId)->name ?? 'N/A' }}</td>
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
                                    <button wire:click="saveAndContinue" class="btn bg-gradient-primary" 
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
                <a href="{{ route('HR.anp.analysis.network.define', $analysis->id) }}" class="btn btn-outline-secondary">
                    <i class="material-icons text-sm">arrow_back</i> Kembali ke Definisi Jaringan
                </a>
            </div>
        </div>
    </div>
</div>