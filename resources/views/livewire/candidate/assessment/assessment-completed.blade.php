<div>
    <div class="card shadow-sm border-0 my-5">
        <div class="card-body text-center p-lg-5 p-4">
            <div class="mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </div>
            <h4 class="mb-3 font-weight-bolder">Tes Telah Selesai!</h4>
            <p class="text-lg text-secondary">
                Anda telah berhasil menyelesaikan <strong>{{ $testName ?? 'Asesmen Ini' }}</strong>.
            </p>
            <p class="text-sm text-muted">
                Hasil tes Anda sedang diproses. Anda akan diarahkan kembali ke daftar asesmen.
            </p>
            <a href="{{ route('candidate.assessment.test') }}" class="btn btn-primary mt-4">
                <i class="material-icons text-sm me-1">arrow_back</i>
                Kembali ke Daftar Asesmen
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            setTimeout(function() {
                if (document.querySelector('[wire\\:id]')) { 
                    window.location.href = "{{ route('candidate.assessment.test') }}";
                }
            }, 5000); 
        });
    </script>
    @endpush
</div>