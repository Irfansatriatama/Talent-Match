<div class="container-fluid px-2 px-md-4">
    <div class="page-header border-radius-xl mt-4" style="height: 300px; overflow: hidden;">
        <img src="{{ asset('assets') }}/img/sky-bg.jpg" alt="profile_bg_image"
            class="w-100 border-radius-lg">                
    </div>
    <div class="card card-body mx-3 mx-md-4 mt-n6">
        <div class="row gx-4 mb-2">
            <div class="col-auto">
                <div class="avatar avatar-xl position-relative">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=random&color=fff" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
                </div>
            </div>
            <div class="col-auto my-auto">
                <div class="h-100">
                    <h5 class="mb-1">
                        {{ auth()->user()->name }}
                    </h5>
                    <p class="mb-0 font-weight-normal text-sm">
                        Candidate
                    </p>
                </div>
            </div>            
        </div>
        <div class="card card-plain h-100">
            <div class="card-header pb-0 p-3">
                <div class="row">
                    <div class="alert alert-info text-white" role="alert">
                        <h4 class="alert-heading mt-1 text-white">Mohon Perhatian!</h4>
                        <p>Untuk memaksimalkan peluang Anda, mohon pastikan semua informasi profil di bawah ini diisi dengan lengkap dan akurat. Kelengkapan profil Anda sangat penting bagi kami untuk dapat memproses lamaran Anda lebih lanjut.</p>
                        <hr class="horizontal light">
                        <p class="mb-0">Pastikan Anda telah:</p>
                        <ul class="mb-0">
                            <li>Memilih <strong>Posisi yang Dilamar</strong> sesuai dengan minat dan kualifikasi Anda.</li>
                            <li>Menempatkan <strong>File CV</strong> sesuai dengan posisi yang akan anda lamar</li>
                            <li>Menempatkan <strong>File Portofolio, Github, dan informasi lainnya </strong>sebagai nilai tambah penilaian anda.</li>
                            <li>Memastikan <strong>Email</strong> dan <strong>Nomor Telepon</strong> Anda aktif dan dapat dihubungi.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                @if (session('status'))
                <div class="row">
                    <div class="alert alert-success alert-dismissible text-white" role="alert">
                        <span class="text-sm">{{ Session::get('status') }}</span>
                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert"
                            aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                @endif
                @if (Session::has('demo'))
                <div class="row">
                    <div class="alert alert-danger alert-dismissible text-white" role="alert">
                        <span class="text-sm">{{ Session::get('demo') }}</span>
                        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert"
                            aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                @endif
                <form wire:submit='update'>
                    <div class="row">

                        <div class="mb-3 col-md-6">

                            <label class="form-label">Email address</label>
                            <input wire:model.blur="user.email" type="email" class="form-control border border-2 p-2">
                            @error('user.email')
                            <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">

                            <label class="form-label">Name</label>
                            <input wire:model.blur="user.name" type="text" class="form-control border border-2 p-2">
                            @error('user.name')
                            <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">

                            <label class="form-label">Phone</label>
                            <input wire:model.blur="user.phone" type="number" class="form-control border border-2 p-2">
                            @error('user.phone')
                            <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Position Applied For</label>
                            <div class="input-group input-group-outline">
                                <select wire:model="user.job_position_id" class="form-control">
                                    <option value="">Pilih Posisi</option>
                                    @foreach($this->jobPositions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('user.job_position_id')
                                <p class='text-danger inputerror mt-1 text-xs'>{{ $message }} </p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <h6 class="mb-3">Dokumen Pendukung</h6>
                            
                            {{-- CV Upload --}}
                            <div class="mb-4">
                                <label class="form-label">CV (PDF, Max 10MB)</label>
                                
                                @if($existingCv)
                                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                                        <div class="text-white fw-bold">
                                            <i class="material-icons me-2 text-white">description</i>
                                            {{ $existingCv->original_name }}
                                        </div>
                                        <div>
                                            <a href="{{ Storage::url($existingCv->file_path) }}" 
                                            target="_blank" 
                                            class="btn btn-sm btn-primary me-2">
                                                <i class="material-icons">visibility</i> Lihat
                                            </a>
                                            <button wire:click="deleteFile({{ $existingCv->id }})" 
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Hapus CV ini?')">
                                                <i class="material-icons">delete</i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="input-group input-group-outline">
                                    <input type="file" 
                                        wire:model="cv" 
                                        class="form-control" 
                                        accept=".pdf">
                                </div>
                                @error('cv')
                                    <p class='text-danger inputerror mt-1 text-xs'>{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Portfolio Upload --}}
                            <div class="mb-4">
                                <label class="form-label">Portfolio/Sertifikat (PDF, Max 10MB per file)</label>
                                
                                @if($existingPortfolios->count() > 0)
                                    <div class="mb-3">
                                        <h6 class="text-sm">File yang sudah diupload:</h6>
                                        @foreach($existingPortfolios as $portfolio)
                                            <div class="alert alert-info d-flex justify-content-between align-items-center mb-2">
                                                <div class="text-white fw-bold">
                                                    <i class="material-icons me-2 text-white">folder</i>
                                                    {{ $portfolio->original_name }}
                                                </div>
                                                <div>
                                                    <a href="{{ Storage::url($portfolio->file_path) }}" 
                                                    target="_blank" 
                                                    class="btn btn-sm btn-primary me-2">
                                                        <i class="material-icons">visibility</i> Lihat
                                                    </a>
                                                    <button wire:click="deleteFile({{ $portfolio->id }})" 
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Hapus file ini?')">
                                                        <i class="material-icons">delete</i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <div class="input-group input-group-outline">
                                    <input type="file" 
                                        wire:model="portfolios" 
                                        class="form-control" 
                                        accept=".pdf"
                                        multiple>
                                </div>
                                <small class="text-muted">Anda dapat memilih beberapa file sekaligus</small>
                                @error('portfolios.*')
                                    <p class='text-danger inputerror mt-1 text-xs'>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn bg-gradient-dark">Submit</button>
                </form>

            </div>
        </div>


    </div>

</div>