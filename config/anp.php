<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Analytic Network Process (ANP)
    |--------------------------------------------------------------------------
    |
    | File ini berisi nilai-nilai konfigurasi yang digunakan dalam berbagai
    | tahap perhitungan ANP pada sistem Talent Match Anda.
    |
    */

    /**
     * Indeks Acak (Random Index - RI) Saaty.
     * Digunakan untuk menghitung Rasio Konsistensi (Consistency Ratio - CR).
     * Kunci array merepresentasikan ukuran matriks (n), dan nilainya adalah RI.
     * Nilai-nilai ini didasarkan pada tabel standar RI yang umum digunakan.
     * Untuk n=1 dan n=2, CR secara teoritis selalu 0 jika matriksnya konsisten.
     */
    'random_indices' => [
        1  => 0.00,
        2  => 0.00,
        3  => 0.58,  // Beberapa sumber menggunakan 0.52
        4  => 0.90,  // Beberapa sumber menggunakan 0.89
        5  => 1.12,  // Beberapa sumber menggunakan 1.11
        6  => 1.24,  // Beberapa sumber menggunakan 1.25
        7  => 1.32,  // Beberapa sumber menggunakan 1.35
        8  => 1.41,  // Beberapa sumber menggunakan 1.40
        9  => 1.45,
        10 => 1.49,
        11 => 1.51,  // Beberapa sumber menggunakan 1.52
        12 => 1.48,  // Beberapa sumber menggunakan 1.53 atau 1.54
        13 => 1.56,
        14 => 1.57,
        15 => 1.59,  // Beberapa sumber menggunakan 1.58
        // Jika Anda memerlukan untuk n > 15, Anda bisa menambahkannya di sini
        // atau service Anda bisa menggunakan nilai RI terakhir (1.59) sebagai aproksimasi.
    ],

    /**
     * Ambang Batas Rasio Konsistensi (Consistency Ratio - CR).
     * Matriks perbandingan dianggap cukup konsisten jika CR <= nilai ini.
     * Nilai 0.10 (atau 10%) adalah yang paling umum diterima.
     */
    'consistency_ratio_threshold' => 0.10,

    /**
     * Parameter untuk Perhitungan Limit Supermatrix.
     * Limit Supermatrix dihitung dengan memangkatkan Weighted Supermatrix
     * secara iteratif hingga konvergen.
     */
    'limit_supermatrix' => [
        /**
         * Jumlah iterasi maksimum yang diizinkan.
         * Untuk mencegah loop tak terbatas jika matriks tidak konvergen.
         */
        'max_iterations' => 100,

        /**
         * Toleransi untuk memeriksa konvergensi.
         * Jika perbedaan absolut total antara matriks pada iterasi saat ini
         * dan iterasi sebelumnya lebih kecil dari nilai ini, matriks dianggap konvergen.
         */
        'tolerance' => 0.00001, // (1.0e-5)
    ],

    /*
    |--------------------------------------------------------------------------
    | Pengaturan Default Tambahan (Opsional)
    |--------------------------------------------------------------------------
    |
    | Anda bisa menambahkan pengaturan lain di sini jika diperlukan di masa mendatang.
    | Misalnya, default label untuk 'Goal' jika tidak diambil dari JobPosition, dll.
    |
    */
    // 'default_goal_label' => 'Pemilihan Kandidat Terbaik',

    // 'supermatrix_element_ordering' => [
    //     // Cara default untuk mengurutkan elemen dalam supermatrix jika diperlukan
    //     // misalnya 'elements_first', 'alternatives_first'
    //     // Ini biasanya ditangani secara dinamis oleh service, tapi bisa jadi ada default.
    // ],

];