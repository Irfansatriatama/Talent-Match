<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h5 class="mb-0">Perbandingan Alternatif (Kandidat)</h5>
            <p class="text-sm">Bandingkan setiap kandidat berdasarkan kriteria yang dipilih.</p>
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
                <h6 class="text-dark mb-1">Konteks Kriteria</h6>
                <p class="mb-0">Membandingkan kandidat berdasarkan kriteria: <strong class="text-primary">{{ $criterionElement->name }}</strong></p>
            </div>

            @if (count($alternativesToCompare) < 2)
                <div class="alert alert-warning text-white">Minimal 2 kandidat diperlukan untuk perbandingan.</div>
            @else
                <div class="row mt-4">
                    <div class="col-lg-8">
                        <div class="d-flex justify-content-end mb-2">
                             <button wire:click="autoFillMatrix" class="btn btn-sm btn-outline-secondary mb-0">
                                <i class="material-icons text-sm">auto_fix_high</i> Isi Otomatis dari Skor Tes
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-items-center">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kandidat</th>
                                        @foreach ($alternativesToCompare as $colCand)
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="white-space: normal; vertical-align: middle;">{{ $colCand->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Loop dengan tambahan $rowIndex untuk logika readonly --}}
                                    @foreach ($alternativesToCompare as $rowIndex => $rowCand) {{-- <-- PERBAIKAN DI SINI --}}
                                        <tr wire:key="alt-row-{{ $rowCand->id }}">
                                            <td class="text-start text-sm font-weight-bold align-middle">{{ $rowCand->name }}</td>

                                            {{-- Loop dengan tambahan $colIndex untuk logika readonly --}}
                                            @foreach ($alternativesToCompare as $colIndex => $colCand) {{-- <-- PERBAIKAN DI SINI --}}
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
                                    <h6 class="mb-2">Vektor Prioritas Kandidat</h6>
                                    <ul class="list-group">
                                        @foreach ($priorityVector as $candidateId => $weight)
                                            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                <span class="text-sm">{{ collect($alternativesToCompare)->firstWhere('id', $candidateId)->name ?? 'N/A' }}</span>
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
                            {{-- Tombol ini akan menyimpan DAN melanjutkan ke kriteria berikutnya ATAU menyelesaikan proses --}}
                            <button wire:click="calculateAndFinish" class="btn bg-gradient-primary" wire:loading.attr="disabled" {{ $isConsistent === true ? '' : 'disabled' }}>
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