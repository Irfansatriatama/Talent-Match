<div class="container-fluid px-2 px-md-4">
    <div class="page-header border-radius-xl mt-4" style="height: 200px; overflow: hidden; background-image: url('{{ asset('assets') }}/img/sky-bg.jpg'); background-size: cover;">
        <span class="mask bg-gradient-primary opacity-4"></span>
    </div>
    <div class="card card-body mx-3 mx-md-4 mt-n6">
        <div class="row gx-4 mb-2">
            <div class="col-auto">
                <div class="avatar avatar-xl position-relative">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($candidate->name) }}&background=random&color=fff" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
                </div>
            </div>
            <div class="col-auto my-auto">
                <div class="h-100">
                    <h5 class="mb-1">{{ $candidate->name }}</h5>
                    @if($candidate->jobPosition)
                        <span class="badge badge-sm bg-gradient-primary">{{ $candidate->jobPosition->name }}</span>
                    @else
                        <p class="mb-0 font-weight-normal text-sm text-muted">Posisi Belum Dipilih</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="row mt-4">
            {{-- Kolom Informasi Profil --}}
            <div class="col-12 col-xl-6 mb-4 mb-xl-0">
                <div class="card card-plain h-100 border">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-0">Informasi Profil</h6>
                    </div>
                    <div class="card-body p-3">
                        <p class="text-sm">{{ $candidate->profile_summary ?? 'Tidak ada ringkasan profil.' }}</p>
                        <hr class="horizontal gray-light my-4">
                        <ul class="list-group">
                            <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Nama Lengkap:</strong> &nbsp; {{ $candidate->name }}</li>
                            <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Email:</strong> &nbsp; {{ $candidate->email }}</li>
                            <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Telepon:</strong> &nbsp; {{ $candidate->phone ?? '-' }}</li>
                            <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Terdaftar pada:</strong> &nbsp; {{ $candidate->created_at->format('d M Y') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Kolom Hasil Asesmen --}}
            <div class="col-12 col-xl-6">
                <div class="card card-plain h-100 border">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-0">Ringkasan Hasil Asesmen</h6>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group">
                             {{-- --- PERBAIKAN: Looping dan Tampilan Hasil Tes --- --}}
                            @forelse ($testResults as $testName => $result)
                            <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                                <div class="d-flex flex-column">
                                    <h6 class="mb-3 text-sm">{{ $testName }}</h6>
                                    <span class="mb-2 text-xs">Status: 
                                        <span class="text-dark font-weight-bold ms-sm-2">{{ $result['status'] }}</span>
                                    </span>
                                    <span class="mb-2 text-xs">Hasil: 
                                        <span class="text-dark ms-sm-2 font-weight-bold">
                                            {{ $result['summary'] ?? 'N/A' }}
                                            @if(!is_null($result['score']))
                                                (Skor: {{ $result['score'] }})
                                            @endif
                                        </span>
                                    </span>
                                    <span class="text-xs">Selesai pada: 
                                        <span class="text-dark ms-sm-2 font-weight-bold">{{ $result['completed_at'] ?? 'N/A' }}</span>
                                    </span>
                                </div>
                            </li>
                            @empty
                            <p>Belum ada hasil asesmen.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer px-0 pt-4">
            <a href="{{ route('hr.candidates') }}" class="btn btn-outline-primary">Kembali ke Daftar Kandidat</a>
        </div>
    </div>
</div>