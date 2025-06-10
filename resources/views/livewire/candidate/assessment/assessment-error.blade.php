{{-- File: resources/views/livewire/candidate/assessment/assessment-error.blade.php --}}
<div>
    <div class="card shadow-sm border-danger my-5"> {{-- Menggunakan border-danger untuk indikasi error --}}
        <div class="card-body text-center p-lg-5 p-4">
            <div class="mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-x-octagon-fill text-danger" viewBox="0 0 16 16">
                    <path d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146zm-6.106 4.5L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"/>
                </svg>
            </div>
            <h4 class="mb-3 font-weight-bolder">Terjadi Kesalahan</h4>
            @if (!empty($testName))
                <p class="text-lg text-secondary">
                    Tidak dapat memuat <strong>{{ $testName }}</strong> dengan benar.
                </p>
            @endif
            <p class="text-lg text-secondary">
                {{ $errorMessage ?? 'Terjadi masalah saat mencoba memuat data tes. Silakan coba kembali atau hubungi administrator.' }}
            </p>
            <a href="{{ route('candidate.assessment.test') }}" class="btn btn-primary mt-4">
                Kembali ke Daftar Asesmen
            </a>
        </div>
    </div>
</div>