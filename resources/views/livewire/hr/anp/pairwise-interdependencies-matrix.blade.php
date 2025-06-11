<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h5 class="mb-0">Perbandingan Interdependensi</h5>
            <p class="text-sm">Bandingkan elemen sumber berdasarkan kekuatan pengaruhnya terhadap elemen target.</p>
        </div>
        <div class="card-body">
            <ul class="wizard-stepper">
                 <li class="step active"><div class="step-icon"><i class="material-icons">description</i></div><div class="step-title">1. Inisiasi</div></li>
                <li class="step active"><div class="step-icon"><i class="material-icons">hub</i></div><div class="step-title">2. Jaringan</div></li>
                <li class="step active"><div class="step-icon"><i class="material-icons">rule</i></div><div class="step-title">3. Perbandingan</div></li>
                <li class="step"><div class="step-icon"><i class="material-icons">emoji_events</i></div><div class="step-title">4. Hasil</div></li>
            </ul>

            {{-- Konteks Perbandingan yang Diperjelas --}}
            <div class="alert alert-light text-dark p-3 text-center" role="alert">
                <h6 class="text-dark mb-1">Konteks Dependensi</h6>
                <p class="mb-0">Seberapa besar pengaruh masing-masing elemen di <strong class="text-primary">[{{ $dependency->sourceable->name }}]</strong> terhadap <strong class="text-info">[{{ $dependency->targetable->name }}]</strong>?</p>
            </div>

            @if (count($sourceElementsToCompare) < 2)
                <div class="alert alert-warning text-white">
                    Hanya ada satu atau tidak ada elemen sumber dalam dependensi ini, perbandingan tidak diperlukan. Silakan klik "Simpan & Lanjutkan" untuk memprosesnya secara otomatis.
                </div>
                 <button wire:click="saveAndContinue" class="btn bg-gradient-primary">
                    Simpan & Lanjutkan <i class="material-icons text-sm">arrow_forward</i>
                </button>
            @else
                <div class="row mt-4">
                    <div class="col-lg-8">
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
                                    {{-- 1. Tambahkan $rowIndex untuk logika readonly --}}
                                    @foreach ($sourceElementsToCompare as $rowIndex => $rowElement)
                                        <tr wire:key="inter-row-{{ $rowElement->id }}">
                                            <td class="text-start text-sm font-weight-bold align-middle">{{ $rowElement->name }}</td>
                                            
                                            {{-- 2. Tambahkan $colIndex untuk logika readonly --}}
                                            @foreach ($sourceElementsToCompare as $colIndex => $colElement)
                                                <td class="p-1 align-middle" wire:key="inter-cell-{{ $rowElement->id }}-{{ $colElement->id }}">
                                                    <div class="input-group input-group-outline">
                                                        @if ($rowElement->id == $colElement->id)
                                                            <input type="text" class="form-control form-control-sm text-center" value="1" readonly disabled>
                                                        @else
                                                            <input type="number" step="any" min="0.11" max="9"
                                                                {{-- 3. Mengubah .blur menjadi .live untuk update real-time --}}
                                                                wire:model.live="matrixValues.{{ $rowElement->id }}.{{ $colElement->id }}"
                                                                class="form-control form-control-sm text-center @error('matrixValues.'.$rowElement->id.'.'.$colElement->id) is-invalid @enderror"
                                                                
                                                                {{-- 4. Menambahkan kondisi 'readonly' untuk segitiga bawah --}}
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
                    </div>

                    <div class="col-lg-4">
                        <h6>Hasil Kalkulasi</h6>
                        <div class="card bg-gray-100">
                            <div class="card-body">
                                @if ($calculationResult && !isset($calculationResult['error']))
                                    <h6 class="mb-1">Rasio Konsistensi (CR)</h6>
                                    <h5 class="font-weight-bolder @if(!$isConsistent) text-danger @else text-success @endif">
                                        {{ number_format($consistencyRatio, 4) }}
                                    </h5>
                                    @if ($isConsistent)
                                        <span class="badge bg-gradient-success">Konsisten</span>
                                    @else
                                        <span class="badge bg-gradient-danger">Tidak Konsisten</span>
                                        <p class="text-xs text-danger mt-1">Nilai CR harus &lt;= {{ config('anp.consistency_ratio_threshold', 0.10) }}. Harap perbaiki.</p>
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
                        </div>
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