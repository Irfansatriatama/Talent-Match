{{-- PERBAIKAN: Menambahkan Alpine.js untuk fungsionalitas collapse --}}
<div class="col-lg-12 mb-4" x-data="{ open: true }"> {{-- PERBAIKAN: x-data untuk state open/close, default terbuka --}}
    <div class="card border-0 shadow-sm">
        {{-- PERBAIKAN: Header dapat diklik untuk toggle open/close --}}
        <div class="card-header bg-gradient-info text-white" 
             @click="open = !open" 
             style="cursor: pointer;">
            <h6 class="mb-0 text-white d-flex justify-content-between align-items-center">
                <span>
                    <i class="material-icons text-sm align-middle">help_outline</i> 
                    Panduan Perbandingan Berpasangan
                </span>
                {{-- PERBAIKAN: Ikon dinamis berubah sesuai state --}}
                <i class="material-icons text-sm" x-text="open ? 'expand_less' : 'expand_more'">expand_less</i>
            </h6>
        </div>
        
        {{-- PERBAIKAN: Body dibungkus dengan x-show dan x-collapse untuk animasi --}}
        <div x-show="open" x-collapse>
            <div class="card-body">
                <!-- Cara Membaca Matriks -->
                <div class="mb-4">
                    <h6 class="text-dark mb-2">
                        <i class="material-icons text-sm align-middle text-info">visibility</i> 
                        Cara Membaca Matriks
                    </h6>
                    <p class="text-sm text-muted mb-0">
                        Untuk membaca matriks ini, bandingkan elemen pada <strong>BARIS</strong> terhadap elemen pada <strong>KOLOM</strong>. 
                        Nilai yang terisi menunjukkan seberapa lebih penting/baik elemen baris dibandingkan elemen kolom. 
                        Contoh: Jika sel [A,B] bernilai 3, artinya "A tiga kali lebih penting dari B". 
                        Secara otomatis, sel [B,A] akan bernilai 1/3 (0.333).
                    </p>
                </div>

                <!-- Cara Mengisi Matriks -->
                <div class="mb-4">
                    <h6 class="text-dark mb-2">
                        <i class="material-icons text-sm align-middle text-success">edit</i> 
                        Cara Mengisi Matriks
                    </h6>
                    <ol class="text-sm ps-3 mb-0">
                        <li class="mb-2">
                            <strong>Pilih sel mana saja</strong> yang ingin Anda isi (kecuali diagonal utama yang selalu bernilai 1).
                        </li>
                        <li class="mb-2">
                            <strong>Masukkan nilai antara 0.11 hingga 9</strong> sesuai Skala Saaty di samping.
                        </li>
                        <li class="mb-2">
                            <strong>Perhatikan sel pasangan</strong> yang akan terisi otomatis dengan nilai kebalikan (1/nilai).
                        </li>
                        <li class="mb-2">
                            <strong>Fokus pada konsistensi</strong> - Setelah mengisi beberapa sel, klik "Hitung Konsistensi" untuk memeriksa CR.
                        </li>
                    </ol>
                </div>

                <!-- Tips Mencapai Konsistensi -->
                <div class="mb-0">
                    <h6 class="text-dark mb-2">
                        <i class="material-icons text-sm align-middle text-warning">tips_and_updates</i> 
                        Tips Mencapai Konsistensi (CR < 0.1)
                    </h6>
                    
                    <!-- Metode Jangkar -->
                    <div class="alert alert-light border-start border-warning border-3 mb-3">
                        <h6 class="text-warning mb-2">
                            <i class="material-icons text-sm align-middle">anchor</i> 
                            Metode "Jangkar" (Anchor Method)
                        </h6>
                        <ol class="text-sm mb-0 ps-3">
                            <li class="mb-1">
                                <strong>Tentukan satu elemen terpenting</strong> sebagai "jangkar" atau patokan utama.
                            </li>
                            <li class="mb-1">
                                <strong>Bandingkan semua elemen lain</strong> terhadap jangkar ini terlebih dahulu.
                            </li>
                            <li>
                                <strong>Gunakan perbandingan jangkar</strong> untuk memandu perbandingan antar elemen lainnya.
                            </li>
                        </ol>
                    </div>

                    <!-- Logika Transitif -->
                    <div class="alert alert-light border-start border-info border-3 mb-0">
                        <h6 class="text-info mb-2">
                            <i class="material-icons text-sm align-middle">device_hub</i> 
                            Gunakan Logika Transitif
                        </h6>
                        <p class="text-sm mb-2">
                            Jaga konsistensi dengan mengikuti aturan transitif dalam penilaian Anda:
                        </p>
                        <div class="bg-white rounded p-3 border">
                            <p class="text-sm mb-2">
                                <strong>Contoh Praktis:</strong>
                            </p>
                            <ul class="text-sm mb-0">
                                <li class="mb-1">
                                    Jika <code>A = 3 × B</code> (A tiga kali lebih penting dari B)
                                </li>
                                <li class="mb-1">
                                    Dan <code>B = 2 × C</code> (B dua kali lebih penting dari C)
                                </li>
                                <li>
                                    Maka <code>A ≈ 6 × C</code> (A seharusnya sekitar 6 kali lebih penting dari C)
                                </li>
                            </ul>
                        </div>
                        <p class="text-sm text-muted mt-2 mb-0">
                            <i class="material-icons text-sm align-middle">info</i>
                            <strong>Tips:</strong> Tidak harus persis 6, nilai antara 5-7 masih dapat diterima. 
                            Yang penting adalah menjaga urutan dan proporsi relatif yang masuk akal.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PERBAIKAN: Tambahkan Alpine.js jika belum ada di layout --}}
@push('js')
<script src="//unpkg.com/alpinejs" defer></script>
@endpush