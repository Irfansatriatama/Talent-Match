<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            @if (session()->has('message'))
                <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                    <span class="alert-icon align-middle"><i class="material-icons text-md">thumb_up_off_alt</i></span>
                    <span class="alert-text"><strong>Sukses!</strong> {{ session('message') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger text-white alert-dismissible fade show" role="alert">
                    <span class="alert-icon align-middle"><i class="material-icons text-md">error_outline</i></span>
                    <span class="alert-text"><strong>Gagal!</strong> {{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-0">Daftar Sesi Analisis</h5>
                            <p class="text-sm mb-0">Kelola semua sesi analisis ANP yang pernah dibuat.</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('h-r.anp.analysis.create') }}" class="btn bg-gradient-primary mb-0">
                                <i class="material-icons text-sm">add</i>&nbsp;&nbsp;Buat Analisis Baru
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Cari nama analisis...</label>
                                <input wire:model.live.debounce.300ms="searchTerm" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-outline">
                                <select wire:model.live="statusFilter" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="completed">✅ Completed</option>
                                    <option value="calculating">⏳ Calculating</option>
                                    <option value="criteria_comparison_pending">📊 Criteria Pending</option>
                                    <option value="alternatives_pending">👥 Alternatives Pending</option>
                                    <option value="network_pending">🔗 Network Pending</option>
                                    <option value="error">❌ Error</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group input-group-outline">
                                <select wire:model.live="perPage" class="form-control">
                                    <option value="10">10/halaman</option>
                                    <option value="25">25/halaman</option>
                                    <option value="50">50/halaman</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Analisis</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Posisi Jabatan</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal Dibuat</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($analyses as $analysis)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $analysis->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $analysis->hrUser->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $analysis->jobPosition->name ?? 'N/A' }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @php
                                            $statusClass = 'bg-gradient-secondary';
                                            $statusIcon = '';
                                            if ($analysis->status == 'completed') {
                                                $statusClass = 'bg-gradient-success';
                                                $statusIcon = '✅';
                                            } elseif ($analysis->status == 'error') {
                                                $statusClass = 'bg-gradient-danger';
                                                $statusIcon = '❌';
                                            } elseif ($analysis->status == 'calculating') {
                                                $statusClass = 'bg-gradient-info';
                                                $statusIcon = '⏳';
                                            } elseif (str_contains($analysis->status, 'pending')) {
                                                $statusClass = 'bg-gradient-warning';
                                                $statusIcon = '⚠️';
                                            }
                                        @endphp
                                        <span class="badge badge-sm {{ $statusClass }}">
                                            {{ $statusIcon }} {{ str_replace('_', ' ', $analysis->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-normal">
                                            {{ $analysis->created_at->locale('id')->tz('Asia/Jakarta')->isoFormat('D MMMM YYYY, HH:mm') }} WIB
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('h-r.anp.analysis.show', ['anpAnalysis' => $analysis->id]) }}" 
                                           class="text-secondary font-weight-bold text-xs" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Lihat Hasil/Detail">
                                            <i class="material-icons text-sm">visibility</i>
                                        </a>
                                        @if($analysis->status !== 'completed' && $analysis->status !== 'calculating')
                                            <a href="{{ route('h-r.anp.analysis.network.define', ['anpAnalysis' => $analysis->id]) }}" 
                                               class="text-secondary font-weight-bold text-xs mx-2" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Lanjutkan Proses">
                                                <i class="material-icons text-sm">play_arrow</i>
                                            </a>
                                        @endif
                                        <a href="#" 
                                           wire:click="deleteAnalysis({{ $analysis->id }})" 
                                           wire:confirm="Anda yakin ingin menghapus sesi analisis ini secara permanen?" 
                                           class="text-secondary font-weight-bold text-xs" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Hapus Analisis">
                                            <i class="material-icons text-sm">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-secondary mb-0">Belum ada data analisis ANP. Silakan buat yang baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-end pb-0">
                    {{ $analyses->links() }}
                </div>
            </div>
        </div>
    </div>
</div>