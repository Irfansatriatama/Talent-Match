<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header p-3 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="font-weight-bolder mb-0">Manajemen Kandidat</h5>
                            <p class="text-sm text-secondary mb-0">Lihat, filter, dan kelola data kandidat.</p>
                        </div>
                    </div>

                    {{-- Area Filter dan Pencarian --}}
                    <div class="row mt-3 gx-2">
                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Cari Nama/Email...</label>
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-2">
                            <div class="input-group input-group-outline">
                                <select wire:model.live="status" class="form-control">
                                    <option value="">Semua Status Penyelesaian</option>
                                    <option value="completed">Selesai Semua Tes</option>
                                    <option value="in_progress">Sedang Mengerjakan</option>
                                    <option value="not_started">Belum Mulai</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-2">
                            <div class="input-group input-group-outline">
                                <select wire:model.live="mbtiType" class="form-control">
                                    <option value="">Semua Tipe MBTI</option>
                                    @foreach (['INTJ', 'INTP', 'ENTJ', 'ENTP', 'INFJ', 'INFP', 'ENFJ', 'ENFP', 'ISTJ', 'ISFJ', 'ESTJ', 'ESFJ', 'ISTP', 'ISFP', 'ESTP', 'ESFP'] as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kandidat</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Skor Prog.</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">RIASEC</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">MBTI</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Tes</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Posisi Dilamar</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($candidates as $candidate)
                                    @php
                                        // Ambil data tes untuk kemudahan akses
                                        $progTest = $candidate->testProgress->where('test_id', 1)->first();
                                        $riasecTest = $candidate->testProgress->where('test_id', 2)->first();
                                        $mbtiTest = $candidate->testProgress->where('test_id', 3)->first(); // Tambahkan ini
                                        
                                        // Untuk MBTI, ambil dari latestMbtiScore jika tes sudah completed
                                        $mbtiResult = null;
                                        if ($mbtiTest && $mbtiTest->status == 'completed') {
                                            $mbtiResult = $candidate->latestMbtiScore?->mbti_type ?? $mbtiTest->result_summary;
                                        }
                                        
                                        $completionPercentage = $candidate->getTestCompletionPercentage();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $candidate->name }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $candidate->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <p class="text-sm font-weight-bold mb-0">{{ ($progTest && $progTest->status == 'completed') ? ($progTest->score ?? 'N/A') : 'N/A' }}</p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm bg-gradient-info">{{ ($riasecTest && $riasecTest->status == 'completed') ? ($riasecTest->result_summary ?? 'N/A') : 'N/A' }}</span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm bg-gradient-warning">{{ $mbtiResult ?? 'N/A' }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="progress-wrapper w-75 mx-auto">
                                                <div class="progress-info">
                                                    <div class="progress-percentage">
                                                        <span class="text-xs font-weight-bold">{{ round($completionPercentage) }}%</span>
                                                    </div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar @if($completionPercentage == 100) bg-gradient-success @else bg-gradient-info @endif" role="progressbar" style="width: {{ $completionPercentage }}%" aria-valuenow="{{ $completionPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-normal">{{ $candidate->job_position ?? 'Belum Dipilih' }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <a href="{{ route('hr.detail-candidate', ['candidate' => $candidate->id]) }}" class="text-secondary font-weight-bold text-xs" data-bs-toggle="tooltip" data-bs-title="Lihat Detail Kandidat">
                                                <i class="material-icons text-sm">visibility</i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center p-3">
                                            <p class="text-secondary">Tidak ada kandidat yang ditemukan.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination Dinamis --}}
                    <div class="p-3">
                        {{ $candidates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>