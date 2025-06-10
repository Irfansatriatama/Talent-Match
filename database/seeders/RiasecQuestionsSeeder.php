<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class RiasecQuestionsSeeder extends Seeder
{
    public function run()
    {
        $testId = 2;
        
        $riasecQuestions = [
            ['text' => 'Uji kualitas suku cadang sebelum pengiriman', 'dimension' => 'R'],
            ['text' => 'Mempelajari struktur tubuh manusia', 'dimension' => 'I'],
            ['text' => 'Menjadi konduktor dalam paduan suara musik', 'dimension' => 'A'],
            ['text' => 'Memberikan bimbingan karir kepada orang lain', 'dimension' => 'S'],
            ['text' => 'Menjual restoran waralaba kepada perorangan', 'dimension' => 'E'],
            ['text' => 'Mengatur gaji bulanan untuk sebuah kantor', 'dimension' => 'C'],
            ['text' => 'Menyusun batu bata atau ubin', 'dimension' => 'R'],
            ['text' => 'Mempelajari perilaku hewan', 'dimension' => 'I'],
            ['text' => 'Memimpin sebuah drama', 'dimension' => 'A'],
            ['text' => 'Menjadi relawan untuk organisasi non-profit', 'dimension' => 'S'],
            ['text' => 'Menjual barang di pusat perbelanjaan', 'dimension' => 'E'],
            ['text' => 'Mengelola persediaan barang menggunakan gadget', 'dimension' => 'C'],
            ['text' => 'Bekerja di tempat pengeboran minyak lepas pantai', 'dimension' => 'R'],
            ['text' => 'Melakukan riset pada tanaman atau binatang', 'dimension' => 'I'],
            ['text' => 'Merancang karya seni untuk majalah', 'dimension' => 'A'],
            ['text' => 'Membantu orang yang memiliki masalah dengan alkohol atau obat-obatan terlarang', 'dimension' => 'S'],
            ['text' => 'Mengatur operasional sebuah hotel', 'dimension' => 'E'],
            ['text' => 'Menggunakan program komputer untuk mengelola tagihan pelanggan', 'dimension' => 'C'],
            ['text' => 'Merakit komponen elektronik', 'dimension' => 'R'],
            ['text' => 'Mengembangkan metode atau prosedur pengobatan baru', 'dimension' => 'I'],
            ['text' => 'Menulis lagu', 'dimension' => 'A'],
            ['text' => 'Melatih olahraga rutin kepada seseorang', 'dimension' => 'S'],
            ['text' => 'Menjalankan salon kecantikan atau toko potong rambut', 'dimension' => 'E'],
            ['text' => 'Melakukan kontrol terhadap catatan pegawai', 'dimension' => 'C'],
            ['text' => 'Mengoperasikan mesin penggiling di sebuah pabrik', 'dimension' => 'R'],
            ['text' => 'Memimpin sebuah penelitian biologi', 'dimension' => 'I'],
            ['text' => 'Menulis buku atau drama', 'dimension' => 'A'],
            ['text' => 'Membantu orang yang memiliki masalah keluarga', 'dimension' => 'S'],
            ['text' => 'Mengelola sebuah departemen di perusahaan besar', 'dimension' => 'E'],
            ['text' => 'Menghitung dan mencatat data statistik serta numerik lainnya', 'dimension' => 'C'],
            ['text' => 'Memperbaiki keran yang rusak', 'dimension' => 'R'],
            ['text' => 'Mempelajari paus dan hewan laut lainnya', 'dimension' => 'I'],
            ['text' => 'Mempelajari instrumen musik', 'dimension' => 'A'],
            ['text' => 'Mengawasi aktivitas anak-anak di sebuah kegiatan perkemahan', 'dimension' => 'S'],
            ['text' => 'Mengelola sebuah toko pakaian', 'dimension' => 'E'],
            ['text' => 'Mengoperasikan kalkulator', 'dimension' => 'C'],
            ['text' => 'Merakit produk di sebuah pabrik', 'dimension' => 'R'],
            ['text' => 'Bekerja di lab biologi', 'dimension' => 'I'],
            ['text' => 'Menjadi pemeran pengganti di sebuah film atau acara televisi', 'dimension' => 'A'],
            ['text' => 'Mengajari anak-anak membaca', 'dimension' => 'S'],
            ['text' => 'Menjual rumah', 'dimension' => 'E'],
            ['text' => 'Menangani transaksi bank milik nasabah', 'dimension' => 'C'],
            ['text' => 'Memasang ubin rumah', 'dimension' => 'R'],
            ['text' => 'Membuat peta bawah laut', 'dimension' => 'I'],
            ['text' => 'Merancang desain panggung untuk drama', 'dimension' => 'A'],
            ['text' => 'Membantu aktivitas keseharian orang lanjut usia', 'dimension' => 'S'],
            ['text' => 'Menjalankan sebuah toko mainan', 'dimension' => 'E'],
            ['text' => 'Menyimpan catatan penerimaan dan pengiriman barang', 'dimension' => 'C'],
        ];

        foreach ($riasecQuestions as $index => $question) {
            Question::create([
                'test_id' => $testId,
                'question_text' => $question['text'],
                'question_order' => $index + 1,
                'riasec_dimension' => $question['dimension']
            ]);
        }
    }
}