<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h5 class="mb-0">Perbandingan Berpasangan</h5>
            <p class="text-sm">Bandingkan tingkat kepentingan relatif antar item di bawah ini.</p>
        </div>
        <div class="card-body">
            <ul class="wizard-stepper">
                 <li class="step active">
                    <div class="step-icon"><i class="material-icons">description</i></div>
                    <div class="step-title">1. Inisiasi</div>
                </li>
                <li class="step active">
                    <div class="step-icon"><i class="material-icons">hub</i></div>
                    <div class="step-title">2. Jaringan</div>
                </li>
                <li class="step active">
                    <div class="step-icon"><i class="material-icons">rule</i></div>
                    <div class="step-title">3. Perbandingan</div>
                </li>
                <li class="step">
                    <div class="step-icon"><i class="material-icons">emoji_events</i></div>
                    <div class="step-title">4. Hasil</div>
                </li>
            </ul>

            @if (count($elementsToCompare) < 2)
                <div class="alert alert-warning text-white">Minimal 2 item diperlukan untuk perbandingan. Silakan kembali ke halaman Definisi Jaringan untuk menambahkan elemen/cluster.</div>
            @else
                <div class="row">
                    <div class="col-lg-8">
                        <h6>Konteks: Membandingkan {{ $elementTypeToCompare == App\Models\AnpElement::class ? 'Elemen' : 'Cluster' }} terhadap 
                            <strong>
                            @if ($controlCriterionContext === 'goal')
                                Goal Utama ({{ $analysis->jobPosition->name }})
                            @elseif ($controlCriterionObject)
                                {{ $controlCriterionObject->name }}
                            @endif
                            </strong>
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-items-center">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Elemen</th>
                                        @foreach ($elementsToCompare as $colElement)
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="white-space: normal; vertical-align: middle;">{{ $colElement->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($elementsToCompare as $rowElement)
                                        <tr>
                                            <td class="text-start text-sm font-weight-bold">{{ $rowElement->name }}</td>
                                            @foreach ($elementsToCompare as $colElement)
                                                <td class="p-1">
                                                    @if ($rowElement->id == $colElement->id)
                                                        <div class="input-group input-group-outline">
                                                            <input type="text" class="form-control form-control-sm text-center" value="1" readonly disabled>
                                                        </div>
                                                    @else
                                                        <div class="input-group input-group-outline">
                                                            <input type="number" step="any" min="0.11" max="9"
                                                                   wire:model.blur="matrixValues.{{ $rowElement->id }}.{{ $colElement->id }}"
                                                                   class="form-control form-control-sm text-center @error('matrixValues.'.$rowElement->id.'.'.$colElement->id) is-invalid @enderror">
                                                        </div>
                                                    @endif
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
                                        <p class="text-xs text-danger mt-1">Nilai CR harus <= {{ config('anp.consistency_ratio_threshold', 0.10) }}. Harap perbaiki nilai perbandingan.</p>
                                    @endif
                                    <hr class="horizontal dark my-3">
                                    <h6 class="mb-2">Vektor Prioritas (Bobot)</h6>
                                    <ul class="list-group">
                                        @foreach ($priorityVector as $elementId => $weight)
                                            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                <span class="text-sm">{{ collect($elementsToCompare)->firstWhere('id', $elementId)->name ?? 'N/A' }}</span>
                                                <span class="text-sm fw-bold">{{ number_format($weight, 4) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif(isset($calculationResult['error']))
                                    <div class="alert alert-danger text-white p-2 text-sm">{{ $calculationResult['error'] }}</div>
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
                 <a href="{{ route('hr.anp.analysis.network.define', $analysis->id) }}" class="btn btn-outline-secondary">
                    <i class="material-icons text-sm">arrow_back</i> Kembali ke Definisi Jaringan
                </a>
            </div>
        </div>
    </div>
</div>