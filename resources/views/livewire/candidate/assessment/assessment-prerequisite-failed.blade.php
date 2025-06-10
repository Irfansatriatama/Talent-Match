{{-- File: resources/views/livewire/candidate/assessment/assessment-prerequisite-failed.blade.php --}}
<div>
    <div class="card shadow-sm border-warning my-5"> {{-- Menggunakan border-warning untuk indikasi --}}
        <div class="card-body text-center p-lg-5 p-4">
            <div class="mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-exclamation-triangle-fill text-warning" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
            </div>
            <h4 class="mb-3 font-weight-bolder">Akses Ditolak</h4>
            @if (!empty($testName))
                <p class="text-lg text-secondary">
                    Anda belum bisa memulai <strong>{{ $testName }}</strong>.
                </p>
            @endif
            <p class="text-lg text-secondary">
                {{ $errorMessage ?? 'Anda harus menyelesaikan tahapan asesmen sebelumnya terlebih dahulu.' }}
            </p>
            <a href="{{ route('candidate.assessment.test') }}" class="btn btn-primary mt-4">
                <i class="material-icons text-sm me-1">arrow_back</i>
                Kembali ke Daftar Asesmen
            </a>
        </div>
    </div>
</div>