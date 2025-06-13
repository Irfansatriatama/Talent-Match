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
                            <li>Mengisi <strong>Ringkasan Profil (Profile Summary)</strong> yang menjelaskan tentang diri Anda secara singkat dan menarik.</li>
                            <li>Menempatkan <strong>Link Portofolio, Github, dan informasi lainnya </strong>sebagai nilai tambah penilaian anda.</li>
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

                            <label for="floatingTextarea2">Profile Summary</label>
                            <textarea wire:model.blur="user.profile_summary" class="form-control border border-2 p-2"
                                placeholder=" Say something about yourself" id="floatingTextarea2" rows="4"
                                cols="50"></textarea>
                            @error('user.about')
                            <p class='text-danger inputerror'>{{ $message }} </p>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn bg-gradient-dark">Submit</button>
                </form>

            </div>
        </div>


    </div>

</div>