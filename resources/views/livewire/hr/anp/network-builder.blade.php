<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header p-3">
            <h5 class="mb-0">Pembangun Jaringan Keputusan (Network Builder)</h5>
            <p class="text-sm">Definisikan kriteria (elemen), kelompokkan (cluster), dan tentukan hubungan antar keduanya (interdependensi).</p>
        </div>
        <div class="card-body">
            <x-anp-stepper currentStep="2" />

            <div class="row">
                <div class="col-lg-5">
                    {{-- Form Tambah Cluster --}}
                    <h6><i class="material-icons text-sm">create_new_folder</i> Tambah Cluster</h6>
                    <form wire:submit.prevent="addCluster" class="p-3 border rounded mb-4">
                        <div class="input-group input-group-outline mb-2">
                            <label class="form-label">Nama Cluster Baru</label>
                            <input type="text" wire:model.lazy="newClusterName" class="form-control">
                        </div>
                        @error('newClusterName') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        <div class="text-end">
                            <button type="submit" class="btn btn-sm bg-gradient-dark mb-0">Tambah</button>
                        </div>
                    </form>

                    {{-- Form Tambah Elemen --}}
                    <h6><i class="material-icons text-sm">post_add</i> Tambah Elemen (Kriteria)</h6>
                    <form wire:submit.prevent="addElement" class="p-3 border rounded mb-4">
                        <div class="input-group input-group-outline mb-2">
                            <label class="form-label">Nama Elemen Baru</label>
                            <input type="text" wire:model.lazy="newElementName" class="form-control">
                        </div>
                        @error('newElementName') <span class="text-danger text-xs mb-2 d-block">{{ $message }}</span> @enderror

                        <div class="input-group input-group-outline mb-2">
                            <select wire:model.lazy="selectedClusterForNewElement" class="form-control">
                                <option value="">-- Tanpa Cluster --</option>
                                @foreach ($allClusters as $cluster)
                                    <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-sm bg-gradient-dark mb-0">Tambah</button>
                        </div>
                    </form>
                </div>

                <div class="col-lg-7">
                    <h6><i class="material-icons text-sm">account_tree</i> Struktur Jaringan Saat Ini</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group mb-3">
                                <li class="list-group-item list-group-item-dark fw-bold">Daftar Cluster</li>
                                @forelse ($allClusters as $cluster)
                                    <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                        {{ $cluster->name }}
                                        <button wire:click="deleteCluster({{ $cluster->id }})" wire:confirm="Yakin?" class="btn btn-link text-danger text-gradient p-0 m-0"><i class="material-icons">delete</i></button>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-xs text-secondary">Belum ada cluster</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group mb-3">
                                <li class="list-group-item list-group-item-dark fw-bold">Daftar Elemen</li>
                                @forelse ($allElements as $element)
                                    <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                        <span>
                                            {{ $element->name }}
                                            @if($element->cluster) <span class="badge bg-light text-dark">{{ $element->cluster->name }}</span> @endif
                                        </span>
                                        <button wire:click="deleteElement({{ $element->id }})" wire:confirm="Yakin?" class="btn btn-link text-danger text-gradient p-0 m-0"><i class="material-icons">delete</i></button>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-xs text-secondary">Belum ada elemen</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    
                    {{-- Form Tambah Dependensi --}}
                    <h6><i class="material-icons text-sm">sync_alt</i> Tambah Interdependensi</h6>
                    <form wire:submit.prevent="addDependency" class="p-3 border rounded">
                        <div class="row align-items-center">
                            <div class="col-5">
                                <label class="text-xs">Sumber Pengaruh</label>
                                <div class="input-group input-group-outline">
                                    <select wire:model.live="sourceType" class="form-control mb-1">
                                        <option value="cluster">Cluster</option>
                                        <option value="element">Elemen</option>
                                    </select>
                                </div>
                                <div class="input-group input-group-outline">
                                    <select wire:model="sourceId" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        @if ($sourceType == 'element')
                                            @foreach ($allElements as $item) <option value="{{ $item->id }}">{{ $item->name }}</option> @endforeach
                                        @else
                                            @foreach ($allClusters as $item) <option value="{{ $item->id }}">{{ $item->name }}</option> @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-2 text-center"><i class="material-icons">arrow_forward</i></div>
                            <div class="col-5">
                                <label class="text-xs">Target Dipengaruhi</label>
                                <div class="input-group input-group-outline">
                                    <select wire:model.live="targetType" class="form-control mb-1">
                                        <option value="cluster">Cluster</option>
                                        <option value="element">Elemen</option>
                                    </select>
                                </div>
                                <div class="input-group input-group-outline">
                                    <select wire:model="targetId" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        @if ($targetType == 'element')
                                            @foreach ($allElements as $item) <option value="{{ $item->id }}">{{ $item->name }}</option> @endforeach
                                        @else
                                            @foreach ($allClusters as $item) <option value="{{ $item->id }}">{{ $item->name }}</option> @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-sm bg-gradient-dark mb-0">Tambah Hubungan</button>
                        </div>
                    </form>
                    <ul class="list-group mt-3">
                        <li class="list-group-item list-group-item-dark fw-bold">Daftar Interdependensi</li>
                         @forelse ($dependencies as $dependency)
                            <li class="list-group-item d-flex justify-content-between align-items-center text-sm">
                                <span>
                                    <strong>{{ $dependency->sourceable->name ?? 'N/A' }}</strong> &rarr; <strong>{{ $dependency->targetable->name ?? 'N/A' }}</strong>
                                </span>
                                <button wire:click="deleteDependency({{ $dependency->id }})" wire:confirm="Yakin?" class="btn btn-link text-danger text-gradient p-0 m-0"><i class="material-icons">delete</i></button>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-xs text-secondary">Belum ada interdependensi.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button wire:click="proceedToCriteriaComparison" class="btn bg-gradient-primary">
                    Lanjutkan ke Perbandingan Kriteria <i class="material-icons text-sm">arrow_forward</i>
                </button>
            </div>
        </div>
    </div>
</div>