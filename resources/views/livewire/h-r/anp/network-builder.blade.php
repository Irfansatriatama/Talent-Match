<div class="card-header p-3">
    <div class="card">
        {{-- Bagian Header dan Panduan tidak berubah --}}
        <div class="card-header p-3">
            <h5 class="mb-0">Pembangun Jaringan Keputusan (Network Builder)</h5>
            <p class="text-sm mb-0">Definisikan kriteria (elemen), kelompokkan (cluster), dan tentukan hubungan antar keduanya (interdependensi).</p>
        </div>
        <div class="card-body p-3 p-md-4">
            <x-anp-stepper currentStep="2" />

            <div class="alert alert-info text-white mb-4 shadow-sm">
                <h6 class="text-white mb-2"><i class="material-icons text-sm align-middle">lightbulb</i> Panduan Pembuatan Jaringan ANP</h6>
                <ol class="mb-0 ps-3">
                    <li class="mb-2"><strong>Langkah 1: Buat Cluster & Elemen.</strong><br>Kelompokkan kriteria Anda ke dalam cluster (misal: 'Hard Skills') dan masukkan kriteria spesifik (elemen) ke dalamnya.</li>
                    <li class="mb-2"><strong>Langkah 2 (Opsional): Definisikan Interdependensi.</strong><br>Jika ada kriteria/cluster yang saling mempengaruhi, hubungkan pada bagian interdependensi.</li>
                    <li><strong>Langkah 3: Lanjutkan.</strong><br>Setelah struktur jaringan selesai, klik tombol 'Lanjutkan ke Perbandingan Kriteria'.</li>
                </ol>
            </div>

            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header p-3 bg-gradient-light">
                    <h6 class="mb-0 d-flex align-items-center"><i class="material-icons text-sm me-2 text-primary">account_tree</i>Kelola Cluster & Elemen</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-lg-6 border-end-lg">
                            <div class="mb-3">
                                <h6 class="text-sm fw-bold text-dark">Tambah Cluster Baru</h6>
                                <form wire:submit.prevent="addCluster">
                                    <div class="d-flex gap-2">
                                        <div class="input-group input-group-outline flex-grow-1">
                                            <input type="text" wire:model.lazy="newClusterName" class="form-control" placeholder="Nama Cluster Baru">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-hover-transform mb-0">
                                            <i class="material-icons text-xs me-1">add_circle</i>
                                        </button>
                                    </div>
                                    @error('newClusterName') <span class="text-danger text-xs mt-1 fade-in">{{ $message }}</span> @enderror
                                </form>
                            </div>

                            <div class="mt-4">
                                <h6 class="text-sm fw-bold d-flex justify-content-between align-items-center">
                                    <span class="text-dark">Daftar Cluster</span>
                                    <span class="badge bg-gradient-primary rounded-pill">{{ count($allClusters) }}</span>
                                </h6>
                                <ul class="list-group list-group-flush custom-scrollbar" style="max-height: 250px; overflow-y: auto;">
                                    @forelse ($allClusters as $cluster)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2 hover-bg-light">
                                            <span class="text-sm d-flex align-items-center" title="{{ $cluster->name }}">
                                                <i class="material-icons text-xs text-primary me-2">folder</i>
                                                {{ $cluster->name }}
                                            </span>
                                            <button wire:click="deleteCluster({{ $cluster->id }})" 
                                                    wire:confirm="Yakin ingin menghapus cluster ini?" 
                                                    class="btn btn-icon-only mb-0 btn-delete-icon" 
                                                    title="Hapus Cluster">
                                                <i class="material-icons text-sm">delete</i>
                                            </button>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-center text-secondary py-4">
                                            <i class="material-icons mb-2 opacity-5">folder_open</i>
                                            <p class="text-xs mb-0">Belum ada cluster</p>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-6 mt-4 mt-lg-0">
                            <div class="mb-3">
                                <h6 class="text-sm fw-bold text-dark">Tambah Elemen (Kriteria) Baru</h6>
                                <form wire:submit.prevent="addElement">
                                    <div class="input-group input-group-outline mb-2">
                                        <input type="text" wire:model.lazy="newElementName" class="form-control" placeholder="Nama Elemen Baru">
                                    </div>
                                    @error('newElementName') <span class="text-danger text-xs mb-2 d-block fade-in">{{ $message }}</span> @enderror

                                    <div class="d-flex gap-2">
                                        <div class="input-group input-group-outline flex-grow-1">
                                            <select wire:model.lazy="selectedClusterForNewElement" class="form-control ps-2">
                                                <option value="">-- Pilih Cluster --</option>
                                                @foreach ($allClusters as $cluster)
                                                    <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-hover-transform mb-0">
                                            <i class="material-icons text-xs me-1">add_circle</i>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="mt-4">
                                <h6 class="text-sm fw-bold d-flex justify-content-between align-items-center">
                                    <span class="text-dark">Daftar Elemen</span>
                                    <span class="badge bg-gradient-primary rounded-pill">{{ count($allElements) }}</span>
                                </h6>
                                <ul class="list-group list-group-flush custom-scrollbar" style="max-height: 250px; overflow-y: auto;">
                                    @forelse ($allElements as $element)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2 hover-bg-light">
                                            <div>
                                                <span class="text-sm d-block fw-medium d-flex align-items-center" title="{{ $element->name }}">
                                                    <i class="material-icons text-xs text-info me-2">label</i>
                                                    {{ $element->name }}
                                                </span>
                                                @if($element->cluster)
                                                    <span class="badge bg-gradient-info text-xxs mt-1 ms-4">{{ $element->cluster->name }}</span>
                                                @else
                                                     <span class="badge bg-light text-secondary text-xxs mt-1 ms-4">Tanpa Cluster</span>
                                                @endif
                                            </div>
                                            <button wire:click="deleteElement({{ $element->id }})" 
                                                    wire:confirm="Yakin ingin menghapus elemen ini?" 
                                                    class="btn btn-icon-only mb-0 btn-delete-icon" 
                                                    title="Hapus Elemen">
                                                <i class="material-icons text-sm">delete</i>
                                            </button>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-center text-secondary py-4">
                                            <i class="material-icons mb-2 opacity-5">view_list</i>
                                            <p class="text-xs mb-0">Belum ada elemen</p>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                 <div class="card-header p-3 bg-gradient-light">
                    <h6 class="mb-0 d-flex align-items-center"><i class="material-icons text-sm me-2 text-primary">sync_alt</i>Kelola Interdependensi (Opsional)</h6>
                </div>
                <div class="card-body p-3">
                    <div class="alert alert-light mb-4">
                        <h6 class="text-dark mb-2">
                            <i class="material-icons text-sm align-middle">info</i> 
                            <a class="text-decoration-none text-dark collapse-toggle" data-bs-toggle="collapse" href="#panduanInterdependensi" 
                               role="button" aria-expanded="false" aria-controls="panduanInterdependensi">
                                Panduan Interdependensi
                                <i class="material-icons text-sm float-end">expand_more</i>
                            </a>
                        </h6>
                        <div class="collapse" id="panduanInterdependensi">
                            <p class="text-sm mb-2">Interdependensi menunjukkan hubungan pengaruh antar kriteria atau cluster dalam jaringan ANP. <strong>Fitur ini OPSIONAL</strong> - Anda dapat melewatinya jika tidak ada hubungan saling mempengaruhi antar kriteria.</p>
                            
                            <div class="bg-white rounded p-3 mb-3 border">
                                <h6 class="text-sm fw-bold text-primary mb-2">⚠️ Penting untuk Perbandingan Interdependensi:</h6>
                                <p class="text-sm mb-2">Agar muncul perbandingan interdependensi, Anda harus membuat <strong>minimal 2 hubungan dengan sumber yang sama</strong>. Contoh:</p>
                                <ul class="text-sm mb-0">
                                    <li>✅ <strong>Benar:</strong> Kepemimpinan → Komunikasi | Kerjasama Tim → Komunikasi<br>
                                        <span class="text-muted ms-3">(2 hubungan terhadap "Komunikasi", akan muncul perbandingan)</span></li>
                                    <li>❌ <strong>Salah:</strong> Hanya Komunikasi → Kepemimpinan<br>
                                        <span class="text-muted ms-3">(Hanya 1 hubungan, tidak ada perbandingan)</span></li>
                                </ul>
                            </div>
                            
                            <p class="text-sm fw-bold mb-1">Jenis Hubungan Interdependensi:</p>
                            <ul class="text-sm mb-2">
                                <li><strong>Cluster → Cluster:</strong> Semua elemen dalam cluster sumber mempengaruhi semua elemen dalam cluster target</li>
                                <li><strong>Cluster → Elemen:</strong> Semua elemen dalam cluster mempengaruhi elemen tertentu</li>
                                <li><strong>Elemen → Cluster:</strong> Satu elemen mempengaruhi semua elemen dalam cluster</li>
                                <li><strong>Elemen → Elemen:</strong> Satu elemen mempengaruhi elemen lainnya</li>
                            </ul>
                            
                            <p class="text-sm mb-0"><strong>💡 Tips:</strong> Mulai dengan hubungan sederhana antar elemen. Jika ragu, Anda dapat melewati bagian ini dan langsung ke perbandingan kriteria.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-7 border-end-lg">
                             <h6 class="text-sm fw-bold mb-3 text-dark">Tambah Hubungan Baru</h6>
                             <form wire:submit.prevent="addDependency">
                                <div class="row align-items-center">
                                    <div class="col-md-5 mb-3 mb-md-0">
                                        <label class="form-label text-xs fw-bold text-dark">Sumber Pengaruh</label>
                                        <div class="input-group input-group-outline mb-2">
                                            <select wire:model.live="sourceType" class="form-control ps-2">
                                                <option value="cluster">Cluster</option>
                                                <option value="element">Elemen</option>
                                            </select>
                                        </div>
                                        <div class="input-group input-group-outline">
                                            <select wire:model="sourceId" class="form-control ps-2">
                                                <option value="">-- Pilih {{ $sourceType == 'element' ? 'Elemen' : 'Cluster' }} --</option>
                                                @if ($sourceType == 'element')
                                                    @foreach ($allElements as $item)<option value="{{ $item->id }}">{{ $item->name }} @if($item->cluster) ({{ $item->cluster->name }}) @endif</option>@endforeach
                                                @else
                                                    @foreach ($allClusters as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <i class="material-icons text-primary pulse-animation" style="font-size: 36px;">arrow_forward</i>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label text-xs fw-bold text-dark">Target Dipengaruhi</label>
                                        <div class="input-group input-group-outline mb-2">
                                            <select wire:model.live="targetType" class="form-control ps-2">
                                                <option value="cluster">Cluster</option>
                                                <option value="element">Elemen</option>
                                            </select>
                                        </div>
                                        <div class="input-group input-group-outline">
                                            <select wire:model="targetId" class="form-control ps-2">
                                                <option value="">-- Pilih {{ $targetType == 'element' ? 'Elemen' : 'Cluster' }} --</option>
                                                @if ($targetType == 'element')
                                                    @foreach ($allElements as $item)<option value="{{ $item->id }}">{{ $item->name }} @if($item->cluster) ({{ $item->cluster->name }}) @endif</option>@endforeach
                                                @else
                                                    @foreach ($allClusters as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary btn-hover-transform mb-0">
                                        <i class="material-icons text-xs me-1">add_link</i> Tambah Hubungan
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-5 mt-4 mt-lg-0">
                            <h6 class="text-sm fw-bold d-flex justify-content-between align-items-center">
                                <span class="text-dark">Daftar Interdependensi</span>
                                <span class="badge bg-gradient-primary rounded-pill">{{ count($dependencies) }}</span>
                            </h6>
                            <ul class="list-group list-group-flush custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
                                @forelse ($dependencies as $dependency)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2 hover-bg-light">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge {{ str_contains($dependency->sourceable_type, 'AnpCluster') ? 'bg-gradient-success' : 'bg-gradient-warning' }} rounded-pill me-1">{{ str_contains($dependency->sourceable_type, 'AnpCluster') ? 'C' : 'E' }}</span>
                                                    <span class="text-sm fw-bold">{{ $dependency->sourceable->name ?? 'N/A' }}</span>
                                                </div>
                                                <i class="material-icons text-sm text-secondary my-1 d-block arrow-animation" style="margin-left: 10px;">subdirectory_arrow_right</i>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge {{ str_contains($dependency->targetable_type, 'AnpCluster') ? 'bg-gradient-success' : 'bg-gradient-warning' }} rounded-pill me-1">{{ str_contains($dependency->targetable_type, 'AnpCluster') ? 'C' : 'E' }}</span>
                                                    <span class="text-sm fw-bold">{{ $dependency->targetable->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button wire:click="deleteDependency({{ $dependency->id }})" 
                                                wire:confirm="Yakin ingin menghapus hubungan ini?" 
                                                class="btn btn-icon-only mb-0 btn-delete-icon" 
                                                title="Hapus Interdependensi">
                                            <i class="material-icons text-sm">delete</i>
                                        </button>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-secondary py-5">
                                        <i class="material-icons mb-2 opacity-5">link_off</i>
                                        <p class="text-xs mb-0">Belum ada interdependensi</p>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button wire:click="proceedToCriteriaComparison" class="btn bg-gradient-dark">
                    Lanjutkan ke Perbandingan Kriteria <i class="material-icons text-sm ms-1">arrow_forward</i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Tambahkan border hanya untuk layar besar untuk memisahkan kolom */
    @media (min-width: 992px) {
        .border-end-lg {
            border-right: 1px solid #e9ecef;
        }
    }
    
    /* Konsistensi button dengan hover effects */
    .btn-primary {
        background: linear-gradient(195deg, #42424a 0%, #191919 100%);
        border: none;
        box-shadow: 0 3px 3px 0 rgba(0, 0, 0, 0.15), 0 3px 1px -2px rgba(0, 0, 0, 0.2), 0 1px 5px 0 rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 26px -12px rgba(0, 0, 0, 0.4), 0 4px 23px 0 rgba(0, 0, 0, 0.15), 0 8px 10px -5px rgba(0, 0, 0, 0.2);
    }
    
    /* Delete button style - icon only tanpa border */
    .btn-delete-icon {
        background: transparent;
        border: none;
        color: #dc3545;
        transition: all 0.3s ease;
        padding: 0.25rem;
    }
    
    .btn-delete-icon:hover {
        color: #a71d2a;
        transform: scale(1.2);
        background: rgba(220, 53, 69, 0.1);
        border-radius: 50%;
    }
    
    .btn-delete-icon:active {
        transform: scale(0.9);
    }
    
    .btn-delete-icon .material-icons {
        transition: all 0.3s ease;
    }
    
    .btn-delete-icon:hover .material-icons {
        transform: rotate(10deg);
    }
    
    /* Button icon animations */
    .btn-hover-transform {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .btn-hover-transform:hover {
        transform: translateY(-2px);
    }
    
    .btn-hover-scale {
        transition: all 0.2s ease;
    }
    
    .btn-hover-scale:hover {
        transform: scale(1.1);
    }
    
    /* Tombol ikon yang lebih baik */
    .btn-icon-only {
        width: 2rem;
        height: 2rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .btn-icon-only .material-icons {
        font-size: 1rem;
    }
    
    /* Special untuk delete icon */
    .btn-delete-icon.btn-icon-only {
        width: 1.75rem;
        height: 1.75rem;
    }
    
    /* Card improvements */
    .card {
        border: none;
        box-shadow: 0 0 2rem 0 rgba(136, 152, 170, 0.15);
    }
    
    .card-header.bg-gradient-light {
        background: linear-gradient(195deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }
    
    /* List hover effects */
    .hover-bg-light {
        transition: all 0.2s ease;
        border-radius: 0.375rem;
    }
    
    .hover-bg-light:hover {
        background-color: #f8f9fa;
        padding-left: 12px !important;
    }
    
    /* Badge improvements */
    .badge.rounded-pill {
        padding: 0.35em 0.65em;
        font-weight: 400;
    }
    
    .badge.bg-gradient-primary {
        background: linear-gradient(195deg, #42424a 0%, #191919 100%);
    }
    
    .badge.bg-gradient-info {
        background: linear-gradient(195deg, #49a3f1 0%, #1A73E8 100%);
    }
    
    .badge.bg-gradient-success {
        background: linear-gradient(195deg, #66BB6A 0%, #43A047 100%);
    }
    
    .badge.bg-gradient-warning {
        background: linear-gradient(195deg, #FFA726 0%, #FB8C00 100%);
    }
    
    /* Styling untuk list */
    .list-group-flush > .list-group-item {
        border-width: 0 0 1px;
        border-color: #e9ecef;
    }
    
    .list-group-flush > .list-group-item:last-child {
        border-bottom-width: 0;
    }

    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(195deg, #42424a 0%, #191919 100%);
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #191919;
    }

    /* Form improvements */
    .input-group-outline {
        transition: all 0.3s ease;
    }
    
    .input-group-outline:focus-within {
        box-shadow: 0 0 0 0.2rem rgba(66, 66, 74, 0.1);
    }
    
    .form-control {
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #42424a;
        box-shadow: none;
    }

    /* Badge yang lebih kecil untuk info cluster di daftar elemen */
    .badge.text-xxs {
        font-size: 0.65rem;
        padding: 0.25em 0.5em;
    }

    /* Collapsible guide styles */
    .collapse-toggle {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .collapse-toggle:hover {
        color: #42424a !important;
    }

    .collapse-toggle .material-icons {
        transition: transform 0.3s ease;
    }

    .collapse-toggle[aria-expanded="true"] .material-icons {
        transform: rotate(180deg);
    }

    .collapse-toggle[aria-expanded="false"] .material-icons {
        transform: rotate(0deg);
    }

    /* Smooth collapse animation */
    .collapse {
        transition: all 0.3s ease;
    }

    .collapsing {
        transition: height 0.3s ease;
    }
    
    /* Animations */
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    
    @keyframes slideRight {
        0%, 100% {
            transform: translateX(0);
        }
        50% {
            transform: translateX(3px);
        }
    }
    
    .arrow-animation {
        animation: slideRight 2s ease-in-out infinite;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fade-in {
        animation: fadeIn 0.3s ease;
    }
    
    /* Material icon improvements */
    .material-icons.opacity-5 {
        opacity: 0.5;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:load', function () {
    // Smooth scroll untuk lists
    const lists = document.querySelectorAll('.custom-scrollbar');
    lists.forEach(list => {
        list.addEventListener('wheel', (e) => {
            e.preventDefault();
            list.scrollTop += e.deltaY * 0.5;
        });
    });
    
    // Button click feedback
    document.addEventListener('click', function(e) {
        if (e.target.matches('.btn, .btn *')) {
            const btn = e.target.closest('.btn');
            btn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                btn.style.transform = '';
            }, 100);
        }
    });
    
    // Livewire loading states
    Livewire.hook('message.sent', () => {
        document.querySelectorAll('.btn').forEach(btn => {
            if (!btn.disabled) {
                btn.style.opacity = '0.7';
                btn.style.pointerEvents = 'none';
            }
        });
    });
    
    Livewire.hook('message.processed', () => {
        document.querySelectorAll('.btn').forEach(btn => {
            btn.style.opacity = '';
            btn.style.pointerEvents = '';
        });
    });
    
    Livewire.on('refreshComponent', () => {
        // Add fade in effect to new elements
        const newElements = document.querySelectorAll('.list-group-item');
        newElements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateX(-10px)';
            setTimeout(() => {
                el.style.transition = 'all 0.3s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateX(0)';
            }, index * 50);
        });
    });
});
</script>
@endpush