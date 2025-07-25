<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h5 class="mb-0">Buat Sesi Analisis Baru</h5>
            <p class="text-sm">Isi detail dasar untuk memulai proses perbandingan kandidat dengan metode ANP.</p>
        </div>
        <div class="card-body">
            <x-anp-stepper currentStep="1" />

            @if (session()->has('error'))
                <div class="alert alert-danger text-white">{{ session('error') }}</div>
            @endif

            <form wire:submit.prevent="saveAnalysis">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="input-group input-group-outline @error('name') is-invalid @enderror">
                            <input type="text" id="name" wire:model.live="name" class="form-control" placeholder="Masukkan nama analisis" required>
                        </div>
                        @error('name') <div class="text-danger text-xs ps-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="input-group input-group-outline @error('job_position_id') is-invalid @enderror">
                            <select id="job_position_id" wire:model.live="job_position_id" class="form-control">
                                <option value="">-- Pilih Posisi Jabatan (Goal) --</option>
                                @foreach($jobPositions as $position)
                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('job_position_id') <div class="text-danger text-xs ps-1">{{ $message }}</div> @enderror
                    </div>
                </div>


                @if($showCandidateList)
                    <div class="mb-3" wire:key="candidate-section-{{ $job_position_id }}">
                        <label class="form-label fw-bold">Pilih Kandidat (Alternatif)</label>
                        
                        <p class="text-sm text-muted mb-2">
                            Ditemukan <strong>{{ $availableCandidates->count() }}</strong> kandidat yang telah melamar posisi ini 
                            dan sudah menyelesaikan semua {{ $totalTests }} tes yang diwajibkan.
                        </p>
                        
                        @if($availableCandidates->count() > 0)
                            <div class="card border p-3 @error('selected_candidates') border-danger @enderror" style="max-height: 300px; overflow-y: auto;">
                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <button type="button" 
                                                wire:click="$set('selected_candidates', {{ $availableCandidates->pluck('id')->toJson() }})" 
                                                class="btn btn-sm btn-outline-primary me-2">
                                            Pilih Semua
                                        </button>
                                        <button type="button" 
                                                wire:click="$set('selected_candidates', [])" 
                                                class="btn btn-sm btn-outline-secondary">
                                            Batal Pilih Semua
                                        </button>
                                    </div>
                                    
                                    @foreach($availableCandidates as $candidate)
                                        <div class="col-md-6 mb-3" wire:key="candidate-{{ $candidate->id }}">
                                            <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <!-- Checkbox dipindahkan ke luar form-check untuk menghindari conflict -->
                                                    <div class="me-3">
                                                        <input class="form-check-input" 
                                                            type="checkbox" 
                                                            wire:model.live="selected_candidates" 
                                                            value="{{ $candidate->id }}" 
                                                            id="candidate_{{ $candidate->id }}"
                                                            style="width: 1.25rem; height: 1.25rem; margin-top: 0;">
                                                    </div>
                                                    
                                                    <!-- Label area untuk informasi kandidat -->
                                                    <label class="d-flex align-items-center mb-0 flex-grow-1" 
                                                        for="candidate_{{ $candidate->id }}"
                                                        style="cursor: pointer;">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($candidate->name) }}&size=40&background=random"
                                                            class="avatar avatar-sm me-2"
                                                            alt="{{ $candidate->name }}">
                                                        <div>
                                                            <h6 class="mb-0 text-sm fw-bold">{{ $candidate->name }}</h6>
                                                            <p class="text-xs text-muted mb-0">{{ $candidate->email }}</p>
                                                        </div>
                                                    </label>
                                                </div>
                                                
                                                <!-- Button Detail -->
                                                <a href="{{ route('h-r.detail-candidate', ['candidate' => $candidate->id]) }}"
                                                target="_blank"
                                                class="btn btn-sm btn-outline-primary mb-0 ms-2">
                                                    <i class="material-icons text-sm">open_in_new</i> Detail
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Counter pilihan -->
                            @if(count($selected_candidates) > 0)
                                <p class="text-sm text-info mt-2">
                                    <i class="material-icons text-sm align-middle">check_circle</i>
                                    {{ count($selected_candidates) }} kandidat dipilih
                                </p>
                            @endif
                        @else
                            <div class="alert alert-warning text-white">
                                <i class="material-icons text-sm align-middle">warning</i>
                                <strong>Tidak ada kandidat yang memenuhi kriteria untuk posisi ini.</strong>
                                <br>
                                <small>Pastikan ada kandidat yang:</small>
                                <ul class="mb-0 mt-1">
                                    <li>Telah mendaftar untuk posisi {{ $jobPositions->find($job_position_id)->name ?? 'ini' }}</li>
                                    <li>Sudah menyelesaikan semua {{ $totalTests }} tes yang diwajibkan</li>
                                </ul>
                            </div>
                        @endif
                        @error('selected_candidates') <div class="text-danger text-xs ps-1 mt-1">{{ $message }}</div> @enderror
                    </div>
                @else
                    <!-- Pesan instruksi jika posisi belum dipilih -->
                    <div class="alert alert-info text-white">
                        <i class="material-icons text-sm align-middle">info</i>
                        Silakan pilih posisi jabatan terlebih dahulu untuk melihat daftar kandidat yang tersedia.
                    </div>
                @endif
                
                <div class="mb-3">
                    <label class="form-label">Deskripsi (Opsional)</label>
                    <div class="input-group input-group-outline">
                        <textarea id="description" 
                                  wire:model.defer="description" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Tambahkan catatan atau deskripsi untuk analisis ini..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('h-r.anp.analysis.index') }}" class="btn btn-outline-secondary">
                        <i class="material-icons text-sm">arrow_back</i> Kembali
                    </a>
                    <button type="submit" 
                            class="btn bg-gradient-primary" 
                            @if(!$showCandidateList || $availableCandidates->count() < 2 || count($selected_candidates) < 2) disabled @endif>
                        <span wire:loading.remove wire:target="saveAnalysis">
                            <i class="material-icons text-sm">save</i> Simpan & Lanjutkan
                        </span>
                        <span wire:loading wire:target="saveAnalysis">
                            <i class="material-icons text-sm">hourglass_empty</i> Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    // Debug 
    document.addEventListener('livewire:load', function () {
        Livewire.on('debugState', function (data) {
            console.log('Livewire State:', data);
        });
    });
</script>
@endpush

<style>
    /* Pastikan checkbox terlihat dengan benar */
    .form-check-input[type="checkbox"] {
        -webkit-appearance: checkbox !important;
        -moz-appearance: checkbox !important;
        appearance: checkbox !important;
        opacity: 1 !important;
        position: static !important;
        margin: 0 !important;
    }
    
    /* Hover effect untuk container */
    .border:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
</style>