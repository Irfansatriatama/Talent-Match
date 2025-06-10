<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            {{-- Menampilkan notifikasi session --}}
            @if (session()->has('message'))
                <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
             @if (session()->has('error'))
                <div class="alert alert-danger text-white alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Card Utama: Pengaturan Posisi Jabatan --}}
            <div class="card">
                <div class="card-header p-3 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="font-weight-bolder mb-0">Pengaturan Posisi Jabatan</h5>
                            <p class="text-sm text-secondary mb-0">Tambah, ubah, atau hapus posisi jabatan yang tersedia untuk analisis.</p>
                        </div>
                        <button wire:click="create()" class="btn bg-gradient-primary mb-0">
                            <i class="material-icons text-sm">add</i>&nbsp;&nbsp;Tambah Posisi Baru
                        </button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Posisi</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Profil RIASEC Ideal</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Profil MBTI Ideal</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jobPositions as $position)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $position->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ Str::limit($position->description, 50) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if(is_array($position->ideal_riasec_profile))
                                            @foreach($position->ideal_riasec_profile as $code)
                                                <span class="badge badge-sm bg-gradient-info">{{ $code }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                         @if(is_array($position->ideal_mbti_profile))
                                            @foreach($position->ideal_mbti_profile as $type)
                                                <span class="badge badge-sm bg-gradient-warning">{{ $type }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="align-middle text-right">
                                        <button wire:click="edit({{ $position->id }})" class="btn btn-link text-dark px-3 mb-0" href="javascript:;"><i class="material-icons text-sm me-2">edit</i>Edit</button>
                                        <button wire:click="delete({{ $position->id }})" wire:confirm="Anda yakin ingin menghapus posisi ini?" class="btn btn-link text-danger px-3 mb-0" href="javascript:;"><i class="material-icons text-sm me-2">delete</i>Hapus</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center p-3">Belum ada posisi jabatan yang ditambahkan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="p-3">
                        {{ $jobPositions->links() }}
                    </div>
                </div>
            </div>

            {{-- Card Tambahan: Panduan Profil Ideal --}}
            <div class="card mt-4">
                <div class="card-header p-3">
                    <h5 class="mb-0">📚 Panduan Profil Ideal untuk Posisi IT</h5>
                    <p class="text-sm text-secondary mb-0">Rekomendasi berdasarkan karakteristik kepribadian MBTI dan minat RIASEC</p>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionJobProfiles">
                        @php
                        $jobProfiles = [
                            [
                                'title' => 'Software Developer / Programmer',
                                'mbti' => ['INTJ', 'INTP', 'ENTJ', 'ENTP', 'ISTJ', 'ISTP'],
                                'riasec' => ['RIA', 'RIS', 'IRA', 'IRC'],
                                'description' => 'Cocok untuk individu yang analitis, logis, dan menikmati pemecahan masalah kompleks melalui kode.'
                            ],
                            [
                                'title' => 'UI/UX Designer',
                                'mbti' => ['ENFP', 'INFP', 'ENFJ', 'INFJ', 'ISFP'],
                                'riasec' => ['ARI', 'AIR', 'AIS', 'ASI'],
                                'description' => 'Ideal untuk orang kreatif yang memahami kebutuhan pengguna dan dapat menerjemahkannya ke desain yang intuitif.'
                            ],
                            [
                                'title' => 'Data Analyst / Data Scientist',
                                'mbti' => ['ISTJ', 'INTJ', 'INTP', 'ESTJ'],
                                'riasec' => ['IRA', 'IRC', 'CIR', 'ICR'],
                                'description' => 'Sesuai untuk pemikir analitis yang suka menggali insight dari data dan membuat keputusan berbasis fakta.'
                            ],
                            [
                                'title' => 'Project Manager',
                                'mbti' => ['ENTJ', 'ENFJ', 'ESTJ', 'ESFJ'],
                                'riasec' => ['ESC', 'ECS', 'SEC', 'SCE'],
                                'description' => 'Sempurna untuk pemimpin alami yang terorganisir dan mampu mengkoordinasikan tim dengan efektif.'
                            ],
                            [
                                'title' => 'System/Network Administrator',
                                'mbti' => ['ISTJ', 'ISTP', 'INTJ', 'INTP'],
                                'riasec' => ['RIC', 'RCI', 'IRC', 'CRI'],
                                'description' => 'Cocok untuk individu yang teliti, metodis, dan menikmati memelihara sistem agar berjalan optimal.'
                            ],
                            [
                                'title' => 'Quality Assurance Engineer',
                                'mbti' => ['ISTJ', 'ISFJ', 'ESTJ', 'INTJ'],
                                'riasec' => ['CIR', 'CRI', 'ICR', 'RCI'],
                                'description' => 'Ideal untuk orang yang detail-oriented dan memiliki passion untuk memastikan kualitas produk.'
                            ],
                            [
                                'title' => 'Business Analyst',
                                'mbti' => ['ENTJ', 'ESTJ', 'INTJ', 'ISTJ'],
                                'riasec' => ['CES', 'CSE', 'ECS', 'ICS'],
                                'description' => 'Sesuai untuk pemikir strategis yang dapat menjembatani kebutuhan bisnis dengan solusi teknologi.'
                            ],
                            [
                                'title' => 'Cybersecurity Analyst',
                                'mbti' => ['INTJ', 'ISTJ', 'INTP', 'ENTJ'],
                                'riasec' => ['RIC', 'IRC', 'ICR', 'CRI'],
                                'description' => 'Sempurna untuk individu yang analitis, waspada, dan tertarik pada keamanan sistem.'
                            ]
                        ];
                        @endphp
                        @foreach($jobProfiles as $index => $profile)
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }} bg-gray-100" type="button"
                                         data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                                         aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                    <i class="material-icons text-sm me-2">work</i>
                                    <strong>{{ $profile['title'] }}</strong>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                  aria-labelledby="heading{{ $index }}" data-bs-parent="#accordionJobProfiles">
                                <div class="accordion-body">
                                    <p class="text-sm mb-3">{{ $profile['description'] }}</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-xs text-uppercase mb-2">
                                                <i class="material-icons text-sm align-middle">psychology</i> Tipe MBTI Ideal:
                                            </h6>
                                            <div class="mb-3">
                                                @foreach($profile['mbti'] as $mbti)
                                                    <span class="badge bg-gradient-warning me-1 mb-1">{{ $mbti }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-xs text-uppercase mb-2">
                                                <i class="material-icons text-sm align-middle">interests</i> Kode RIASEC Ideal:
                                            </h6>
                                            <div class="mb-3">
                                                @foreach($profile['riasec'] as $riasec)
                                                    <span class="badge bg-gradient-info me-1 mb-1">{{ $riasec }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-light text-dark text-xs mb-0" role="alert">
                                        <strong>💡 Tips:</strong> Gunakan kombinasi ini sebagai panduan saat membuat posisi baru.
                                         Anda dapat menyesuaikan sesuai kebutuhan spesifik perusahaan.
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-2">Keterangan Kode RIASEC:</h6>
                        <ul class="list-unstyled mb-0 text-sm">
                            <li><strong>R (Realistic):</strong> Praktis, berorientasi pada alat dan teknologi</li>
                            <li><strong>I (Investigative):</strong> Analitis, suka riset dan pemecahan masalah</li>
                            <li><strong>A (Artistic):</strong> Kreatif, inovatif, dan ekspresif</li>
                            <li><strong>S (Social):</strong> Suka berinteraksi dan membantu orang lain</li>
                            <li><strong>E (Enterprising):</strong> Kepemimpinan, persuasif, berorientasi bisnis</li>
                            <li><strong>C (Conventional):</strong> Terorganisir, detail, dan sistematis</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal untuk Tambah/Edit Posisi --}}
    @if($isModalOpen)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit Posisi Jabatan' : 'Tambah Posisi Jabatan Baru' }}</h5>
                    <button wire:click="closeModal" type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="store">
                        <div class="mb-3">
                            <label>Nama Posisi Jabatan</label>
                            <div class="input-group input-group-outline">
                                <input wire:model="name" type="text" class="form-control" placeholder="cth: Software Engineer">
                            </div>
                            @error('name') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi</label>
                            <div class="input-group input-group-outline">
                                <textarea wire:model="description" class="form-control" rows="3" placeholder="Jelaskan secara singkat tentang posisi ini"></textarea>
                            </div>
                             @error('description') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                         <div class="mb-3">
                            <label>Profil RIASEC Ideal</label>
                            <div class="input-group input-group-outline">
                                <input wire:model="ideal_riasec_profile" type="text" class="form-control" placeholder="cth: RIA,RIS,IRC (pisahkan dengan koma)">
                            </div>
                            <p class="text-xs text-secondary mb-0">Masukkan 3 huruf kode Holland, pisahkan dengan koma jika lebih dari satu.</p>
                            @error('ideal_riasec_profile') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Profil MBTI Ideal</label>
                            <div class="input-group input-group-outline">
                                <input wire:model="ideal_mbti_profile" type="text" class="form-control" placeholder="cth: INTJ,INTP,ENTJ (pisahkan dengan koma)">
                            </div>
                            <p class="text-xs text-secondary mb-0">Masukkan 4 huruf tipe MBTI, pisahkan dengan koma jika lebih dari satu.</p>
                             @error('ideal_mbti_profile') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal" type="button" class="btn bg-gradient-secondary">Batal</button>
                    <button wire:click.prevent="store" type="button" class="btn bg-gradient-primary">{{ $isEditMode ? 'Simpan Perubahan' : 'Tambahkan' }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>