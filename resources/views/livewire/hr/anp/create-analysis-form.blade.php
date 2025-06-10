<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h5 class="mb-0">Buat Sesi Analisis Baru</h5>
            <p class="text-sm">Isi detail dasar untuk memulai proses perbandingan kandidat dengan metode ANP.</p>
        </div>
        <div class="card-body">
            <ul class="wizard-stepper">
                <li class="step active">
                    <div class="step-icon"><i class="material-icons">description</i></div>
                    <div class="step-title">1. Inisiasi</div>
                </li>
                <li class="step">
                    <div class="step-icon"><i class="material-icons">hub</i></div>
                    <div class="step-title">2. Jaringan</div>
                </li>
                <li class="step">
                    <div class="step-icon"><i class="material-icons">rule</i></div>
                    <div class="step-title">3. Perbandingan</div>
                </li>
                <li class="step">
                    <div class="step-icon"><i class="material-icons">emoji_events</i></div>
                    <div class="step-title">4. Hasil</div>
                </li>
            </ul>

            @if (session()->has('error'))
                <div class="alert alert-danger text-white">{{ session('error') }}</div>
            @endif

            <form wire:submit.prevent="saveAnalysis">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="input-group input-group-outline @error('name') is-invalid @enderror">
                            <label class="form-label">Nama Analisis</label>
                            <input type="text" id="name" wire:model.defer="name" class="form-control">
                        </div>
                        @error('name') <div class="text-danger text-xs ps-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="input-group input-group-outline @error('job_position_id') is-invalid @enderror">
                            <select id="job_position_id" wire:model.defer="job_position_id" class="form-control">
                                <option value="">-- Pilih Posisi Jabatan (Goal) --</option>
                                @foreach($jobPositions as $position)
                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('job_position_id') <div class="text-danger text-xs ps-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label>Pilih Kandidat (Alternatif)</label>
                    <div class="card p-3 @error('selected_candidates') border-danger @enderror" style="max-height: 250px; overflow-y: auto;">
                        <div class="row">
                        @forelse($availableCandidates as $candidate)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.defer="selected_candidates" value="{{ $candidate->id }}" id="candidate_{{ $candidate->id }}">
                                    <label class="form-check-label" for="candidate_{{ $candidate->id }}">
                                        {{ $candidate->name }} <span class="text-xs text-secondary">({{ $candidate->email }})</span>
                                    </label>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-secondary">Tidak ada kandidat yang tersedia.</p>
                        @endforelse
                        </div>
                    </div>
                    @error('selected_candidates') <div class="text-danger text-xs ps-1">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3">
                    <div class="input-group input-group-outline">
                        <textarea id="description" wire:model.defer="description" class="form-control" rows="3" placeholder="Deskripsi (Opsional)"></textarea>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn bg-gradient-primary">
                        <span wire:loading.remove wire:target="saveAnalysis">Simpan & Lanjutkan</span>
                        <span wire:loading wire:target="saveAnalysis">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>