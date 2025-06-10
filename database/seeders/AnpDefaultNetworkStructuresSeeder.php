<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnpNetworkStructure;
use App\Models\AnpCluster;
use App\Models\AnpElement;
use App\Models\AnpDependency;
use Illuminate\Support\Facades\DB;

class AnpDefaultNetworkStructuresSeeder extends Seeder
{
    /**
     * Menjalankan proses seeding untuk membuat struktur jaringan ANP default.
     * Struktur ini akan digunakan sebagai template saat membuat analisis baru.
     *
     * @return void
     */
    public function run(): void
    {
        // Menggunakan updateOrCreate untuk membuat seeder "pintar" (non-destructive)
        // Pertama, buat atau update struktur jaringan utamanya
        $structure = AnpNetworkStructure::updateOrCreate(
            ['name' => 'Struktur Standar untuk Posisi Teknisi'], // Cari berdasarkan nama ini
            [
                'description' => 'Struktur jaringan default yang menyeimbangkan hard skills, soft skills, dan kesesuaian kepribadian. Cocok untuk posisi seperti Software Developer, Data Analyst, dll.',
            ]
        );

        // --- BUAT ATAU UPDATE CLUSTERS ---
        $techCluster = $structure->clusters()->updateOrCreate(
            ['name' => 'Kemampuan Teknis (Hard Skills)'],
            ['description' => 'Kriteria yang berkaitan dengan kemampuan teknis dan pemecahan masalah.']
        );

        $softCluster = $structure->clusters()->updateOrCreate(
            ['name' => 'Kemampuan Interpersonal (Soft Skills)'],
            ['description' => 'Kriteria yang berkaitan dengan komunikasi, kerja tim, dan kolaborasi.']
        );

        $personalityCluster = $structure->clusters()->updateOrCreate(
            ['name' => 'Kecocokan Kepribadian & Minat'],
            ['description' => 'Kriteria yang mengukur kesesuaian kepribadian dan minat kandidat dengan kultur perusahaan dan pekerjaan.']
        );


        // --- BUAT ATAU UPDATE ELEMENTS DI DALAM SETIAP CLUSTER ---
        // Elements untuk Cluster Teknis
        $techCluster->elements()->updateOrCreate(['name' => 'Logika & Algoritma'], ['anp_network_structure_id' => $structure->id, 'description' => 'Kemampuan problem-solving dan berpikir secara logis.']);
        $techCluster->elements()->updateOrCreate(['name' => 'Penguasaan Database'], ['anp_network_structure_id' => $structure->id, 'description' => 'Pemahaman SQL, NoSQL, dan manajemen data.']);
        $techCluster->elements()->updateOrCreate(['name' => 'Kualitas Kode'], ['anp_network_structure_id' => $structure->id, 'description' => 'Kemampuan menulis kode yang bersih, efisien, dan maintainable.']);

        // Elements untuk Cluster Soft Skills
        $softCluster->elements()->updateOrCreate(['name' => 'Komunikasi'], ['anp_network_structure_id' => $structure->id, 'description' => 'Kemampuan menyampaikan ide secara jelas dan efektif.']);
        $softCluster->elements()->updateOrCreate(['name' => 'Kerja Sama Tim'], ['anp_network_structure_id' => $structure->id, 'description' => 'Kemampuan berkolaborasi dengan orang lain.']);

        // Elements untuk Cluster Kepribadian
        $personalityCluster->elements()->updateOrCreate(['name' => 'Kecocokan RIASEC'], ['anp_network_structure_id' => $structure->id, 'description' => 'Kesesuaian minat karir dengan profil pekerjaan.']);
        $personalityCluster->elements()->updateOrCreate(['name' => 'Kecocokan MBTI'], ['anp_network_structure_id' => $structure->id, 'description' => 'Kesesuaian tipe kepribadian dengan lingkungan kerja.']);


        // --- BUAT ATAU UPDATE DEPENDENCIES (INTERDEPENDENSI) ---
        // Ini adalah inti dari ANP
        $structure->dependencies()->updateOrCreate(
            [
                'sourceable_id' => $softCluster->id,
                'sourceable_type' => AnpCluster::class,
                'targetable_id' => $techCluster->id,
                'targetable_type' => AnpCluster::class,
            ],
            ['description' => 'Pengaruh Soft Skills terhadap Kualitas Teknis']
        );
    }
}