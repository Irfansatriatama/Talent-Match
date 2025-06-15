<div>
    <x-anp-stepper currentStep="3" />

    <div class="card card-body mb-4">
        <h5 class="mb-1">Perbandingan Interdependensi</h5>
        <p class="text-sm mb-0">
            Konteks: Seberapa besar pengaruh masing-masing elemen di <strong>[{{ $dependency->sourceable->name }}]</strong> 
            terhadap <strong>[{{ $dependency->targetable->name }}]</strong> untuk analisis "{{ $analysis->name }}".
        </p>
    </div>

    <div class="card mb-4">
        <div class="card-body p-3">
            @if (count($sourceElementsToCompare) < 2)
                <div class="alert alert-warning text-white font-weight-bold">
                    Hanya ada satu atau tidak ada elemen sumber dalam dependensi ini, perbandingan tidak diperlukan.
                </div>
                <button wire:click="saveAndContinue" class="btn bg-gradient-primary">
                    Simpan & Lanjutkan <i class="material-icons text-sm">arrow_forward</i>
                </button>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-items-center">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Elemen Sumber</th>
                                @foreach ($sourceElementsToCompare as $colElement)
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="white-space: normal; vertical-align: middle;">{{ $colElement->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sourceElementsToCompare as $rowIndex => $rowElement)
                                <tr wire:key="row-{{ $rowElement->id }}">
                                    <td class="text-start text-sm font-weight-bold align-middle">
                                        {{ $rowElement->name }}
                                    </td>
                                    @foreach ($sourceElementsToCompare as $colIndex => $colElement)
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
                                                        wire:model.live.debounce.10ms="matrixValues.{{ $rowElement->id }}.{{ $colElement->id }}"
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
            @endif
        </div>
    </div>

    {{-- IMPLEMENTASI PANDUAN PENGISIAN MATRIKS --}}
    <div class="row mb-4">
        <div class="col-12">
            <x-anp-matrix-guide />
        </div>
    </div>

    @if (count($sourceElementsToCompare) >= 2)
        <div class="row">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <x-saaty-scale-guide />
            </div>
            <div class="col-lg-7">
                <div class="card border h-100">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-0">Hasil Kalkulasi</h6>
                    </div>
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="bg-gray-100 p-3 rounded">
                            @if ($isConsistent !== null)
                                <h6 class="mb-1">Rasio Konsistensi (CR)</h6>
                                <h5 class="font-weight-bolder @if(!$isConsistent) text-danger @else text-success @endif">
                                    {{ number_format($consistencyRatio, 4) }}
                                </h5>
                                @if ($isConsistent)
                                    <span class="badge bg-gradient-success">Konsisten</span>
                                @else
                                    <span class="badge bg-gradient-danger">Tidak Konsisten</span>
                                    <p class="text-xs text-danger mt-1">Nilai CR harus <= {{ config('anp.consistency_ratio_threshold', 0.10) }}. Harap perbaiki.</p>
                                @endif
                                <hr class="horizontal dark my-3">
                                <h6 class="mb-2">Vektor Prioritas Pengaruh</h6>
                                <ul class="list-group">
                                    @foreach ($priorityVector as $elementId => $weight)
                                        <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                            <span class="text-sm">{{ collect($sourceElementsToCompare)->firstWhere('id', $elementId)->name ?? 'N/A' }}</span>
                                            <span class="text-sm fw-bold">{{ number_format($weight, 4) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-secondary text-center">Tekan tombol "Hitung Konsistensi" untuk melihat hasilnya di sini.</p>
                            @endif
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-grid gap-2 mt-3">
                                <button wire:click="recalculateConsistency" class="btn btn-outline-info" wire:loading.attr="disabled">
                                    Hitung Konsistensi
                                </button>
                                <button wire:click="saveAndContinue" class="btn bg-gradient-primary" wire:loading.attr="disabled" {{ $isConsistent === true ? '' : 'disabled' }}>
                                    Simpan & Lanjutkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <hr class="horizontal dark my-4">
    <div class="d-flex justify-content-between">
        <a href="{{ route('h-r.anp.analysis.network.define', ['anpAnalysis' => $analysis->id]) }}" class="btn btn-outline-secondary">
            <i class="material-icons text-sm">arrow_back</i> Kembali ke Definisi Jaringan
        </a>
    </div>
</div>