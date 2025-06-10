<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionOption;

class MbtiQuestionsSeeder extends Seeder
{
    public function run()
    {
        $testId = 3;
        
        $mbtiQuestions = [
            // EI Questions (Extrovert vs Introvert)
            [
                'dichotomy' => 'EI',
                'order' => 1,
                'options' => [
                    ['text' => 'Menemukan dan Mengembangkan ide dengan mendiskusikannya', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Menemukan dan mengembangkan ide dengan merenungkan', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 2,
                'options' => [
                    ['text' => 'Lebih memilih berkomunikasi dengan bicara', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Lebih memilih berkomunikasi dengan menulis', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 3,
                'options' => [
                    ['text' => 'Berorientasi pada dunia eksternal (kegiatan, sosial, lingkungan)', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Berorientasi pada dunia internal (pemikiran, memori, ide)', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 4,
                'options' => [
                    ['text' => 'Fokus pada banyak hobi secara luas dan umum', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Fokus pada sedikit hobi namun mendalam', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 5,
                'options' => [
                    ['text' => 'Sosial dan ekspresif', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Tertutup dan mandiri', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 6,
                'options' => [
                    ['text' => 'Bertemu orang dan aktivitas sosial membuat bersemangat', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Pertemuan dengan orang lain dan aktivitas sosial melelahkan', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 7,
                'options' => [
                    ['text' => 'Beraktifitas sendirian di rumah membosankan', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Beraktifitas sendirian di rumah menyenangkan', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 8,
                'options' => [
                    ['text' => 'Berinisiatif tinggi hampir dalam berbagai hal meskipun tidak berhubungan dengan dirinya', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Berinisiatif bila situasi memaksa atau berhubungan dengan kepentingan sendiri', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 9,
                'options' => [
                    ['text' => 'Lebih memilih tempat yang ramai dan banyak interaksi/aktivitas', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Lebih memilih tempat yang tenang danpribadi untuk berkonsentrasi', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 10,
                'options' => [
                    ['text' => 'Berani bertindak tanpa terlalu lama berfikir', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Berpikir secara matang sebelum bertindak', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 11,
                'options' => [
                    ['text' => 'Mengekspresikan semangat', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Menyimpan semangat dalam hati', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 12,
                'options' => [
                    ['text' => 'Memilih berkomunikasi pada sekelompok orang', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Mencari kesempatan untuk berkomunikasi secara perorangan', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 13,
                'options' => [
                    ['text' => 'Lebih suka berkomunikasi langsung (tatap muka)', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Lebih suka berkomunikasi tidak langsung (telpon, surat, e-mail)', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 14,
                'options' => [
                    ['text' => 'Membangun ide pada saat berbicara', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Membangun ide dengan matang baru membicarakannya', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            [
                'dichotomy' => 'EI',
                'order' => 15,
                'options' => [
                    ['text' => 'Spontan, easy going, fleksibel', 'pole' => 'E'], // [cite: 6]
                    ['text' => 'Berhati-hati, penuh pertimbangan, kaku', 'pole' => 'I'] // [cite: 1]
                ]
            ],
            // SN Questions (Sensing vs Intuition)
            [
                'dichotomy' => 'SN',
                'order' => 16,
                'options' => [
                    ['text' => 'Bergerak dari detail ke gambaran umum sebagai kesimpulan akhir', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Bergerak dari gambaran umum baru ke detail', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 17,
                'options' => [
                    ['text' => 'Berbicara mengenai masalahyang dihadapi hari ini dan langkah-langkah praktis mengatasinya', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Berbicara mengenai visi misi masa depan dan konsep-konsep mengenai visi misi tersebut', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 18,
                'options' => [
                    ['text' => 'Menggunakan pengalaman sebagai pedoman', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Menggunakan imajinasi dan perenungan sebagai pedoman', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 19,
                'options' => [
                    ['text' => 'SOP sangat membantu', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'SOP sangat membosankan', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 20,
                'options' => [
                    ['text' => 'Prosedural dan tradisional', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Bebas dan dinamis', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 21,
                'options' => [
                    ['text' => 'Memilih fakta lebih penting daripada ide inspiratif', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Memilih ide inspiratif lebih penting daripada fakta', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 22,
                'options' => [
                    ['text' => 'Kontinuitas dan stabilitas lebih diutamakan', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Perubahan dan variasi lebih diutamakan', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 23,
                'options' => [
                    ['text' => 'Bertindak step by step dengan timeframe yang jelas', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Bertindak dengan semangat tanpa menggunakan timeframe', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 24,
                'options' => [
                    ['text' => 'Menarik kesimpulan dengan lama dan hati hati', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Menarik kesimpulan dengan cepat sesuai naluri', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 25,
                'options' => [
                    ['text' => 'Mengklarifikasi ide dan teori sebelum dipraktekan', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Memahami ide dan teori saat mempraktekannya langsung', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 26,
                'options' => [
                    ['text' => 'Berfokus pada masa kini (yang dapat diubah saat ini)', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Berfokus pada masa depan (apa yang mungkin dicapai di masa depan)', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 27,
                'options' => [
                    ['text' => 'Secara konsisten mengamati dan mengingat detail', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Mengamati dan mengingat detail hanya bila berhubungan dengan pola', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 28,
                'options' => [
                    ['text' => 'Praktis', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Konspetual', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 29,
                'options' => [
                    ['text' => 'Menggunakan keterampilan yang sudah ada', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'Menyukai tantangan untuk menguasai keterampilan baru', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            [
                'dichotomy' => 'SN',
                'order' => 30,
                'options' => [
                    ['text' => 'Memilih cara yang sudah ada dan sudah terbukti', 'pole' => 'S'], // [cite: 2]
                    ['text' => 'memilih cara yang unik dan belum dipraktekkan orang lain', 'pole' => 'N'] // [cite: 7]
                ]
            ],
            // TF Questions (Thinking vs Feeling)
            [
                'dichotomy' => 'TF',
                'order' => 31,
                'options' => [
                    ['text' => 'Objektif', 'pole' => 'T'], // [cite: 3]
                    ['text' => 'Subjektif', 'pole' => 'F'] // [cite: 8]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 32,
                'options' => [
                    ['text' => 'Diyakinkan dengan penjelasan yang masuk akal', 'pole' => 'T'], // [cite: 3]
                    ['text' => 'Diyakinkan dengan penjelasan yang menyentuh perasaan', 'pole' => 'F'] // [cite: 8]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 33,
                'options' => [
                    ['text' => 'Berorientasi tugas dan job description', 'pole' => 'T'], // [cite: 3]
                    ['text' => 'Berorientasi pada manusia dan hubungan', 'pole' => 'F'] // [cite: 8]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 34,
                'options' => [
                    ['text' => 'Mengambil keputusan berdasarkan logika dan aturan main', 'pole' => 'T'], // [cite: 3]
                    ['text' => 'Mengambil keputusan berdasar perasaan pribadi dan kondisi orang lain', 'pole' => 'F'] // [cite: 8]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 35,
                'options' => [
                    ['text' => 'Mengemukakan tujuan dan sasaran lebih dahulu', 'pole' => 'T'], // [cite: 3]
                    ['text' => 'Mengemukakan kesepakatan terlebih dahulu', 'pole' => 'F'] // [cite: 8]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 36,
                'options' => [
                    ['text' => 'Menganalisa', 'pole' => 'T'], // [cite: 3]
                    ['text' => 'Berempati', 'pole' => 'F'] // [cite: 8]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 37,
                'options' => [
                    ['text' => 'Menghargai seseorang karena skill dan faktor sejenis', 'pole' => 'T'], // [cite: 3]
                    ['text' => 'Menghargai seseorang karena sifat dan perilakuknya', 'pole' => 'F'] // [cite: 8]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 38,
                'options' => [
                    ['text' => 'Melibatkan perasaan itu tidak profesional', 'pole' => 'T'], // [cite: 4]
                    ['text' => 'Terlalu kaku pada peraturan dan pekerjaan itu kejam', 'pole' => 'F'] // [cite: 9]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 39,
                'options' => [
                    ['text' => 'Yang penting tujuan tercapai', 'pole' => 'T'], // [cite: 4]
                    ['text' => 'Yang penting situasi harmonis terjaga', 'pole' => 'F'] // [cite: 9]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 40,
                'options' => [
                    ['text' => 'Mempertanyakan', 'pole' => 'T'], // [cite: 4]
                    ['text' => 'Mengakomodasi', 'pole' => 'F'] // [cite: 9]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 41,
                'options' => [
                    ['text' => 'Sering dianggap keras kepala', 'pole' => 'T'], // [cite: 4]
                    ['text' => 'Sering dianggap terlalu memihak', 'pole' => 'F'] // [cite: 9]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 42,
                'options' => [
                    ['text' => 'Bersemangat saat mengkritik dan menemukan kesalahan', 'pole' => 'T'], // [cite: 4]
                    ['text' => 'Bersemangat saat menolong orang keluar dari kesalahan dan meluruskan', 'pole' => 'F'] // [cite: 9]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 43,
                'options' => [
                    ['text' => 'Standar harus ditegakkan di atas segalanya', 'pole' => 'T'], // [cite: 4]
                    ['text' => 'Perasaan manusia lebih penting dari sekedar standar', 'pole' => 'F'] // [cite: 9]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 44,
                'options' => [
                    ['text' => 'Menuntut peprlakuan yang adil dan sama pada semua orang', 'pole' => 'T'], // [cite: 4]
                    ['text' => 'Menuntut perlakukan khusus sesuai karakteristik masing-masing orang', 'pole' => 'F'] // [cite: 9]
                ]
            ],
            [
                'dichotomy' => 'TF',
                'order' => 45,
                'options' => [
                    ['text' => 'Mementingkan sebab-akibat', 'pole' => 'T'], // [cite: 4]
                    ['text' => 'Mementingkan nilai-nilai personal', 'pole' => 'F'] // [cite: 9]
                ]
            ],
            // JP Questions (Judging vs Perceiving)
            [
                'dichotomy' => 'JP',
                'order' => 46,
                'options' => [
                    ['text' => 'Terencana dan memiliki deadline jelas', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Spontan, fleksibel, tidak diikat waktu', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 47,
                'options' => [
                    ['text' => 'Tidak menyukai hal-hal bersifat mendadak dan di luar perencanaan', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Perubahan mendadak tidak jadi masalah', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 48,
                'options' => [
                    ['text' => 'Aturan, jadwal, dan target akan sangat membantu dan memperjelas tindakan', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Aturan, jadwal dan target sangat mengikat dan membebani', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 49,
                'options' => [
                    ['text' => 'Berorientasi pada hasil', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Berorientasi pada proses', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 50,
                'options' => [
                    ['text' => 'Mengatur oroang lain dengan tata tertib agar tujuan tercapai', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Membiarkan oranglain bertindak bebas asalkan tujuan tercapai', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 51,
                'options' => [
                    ['text' => 'Fokus pada target dan mengabaikan hal-hal baru', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Memperhatikan hal-hal baru dan siap menyesuaikan diri serta mengubah target', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 52,
                'options' => [
                    ['text' => 'Berpegang teguh pada pendirian', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Pendirian masih bisa berubah tergantung situasinya nanti', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 53,
                'options' => [
                    ['text' => 'Merasa tenang bila semua sudah diputuskan', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Merasa nyaman bila situasi tetap terbuka terhadap pilihan-pilihan lain', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 54,
                'options' => [
                    ['text' => 'Ketidakpastian membuat bingung dan meresahkan', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Ketidakpastianitu seru, mengegangkan, dan membuat hati lebih senang', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 55,
                'options' => [
                    ['text' => 'Situasi ""last minute"" sangat menyiksa, membuat stress dan merupakan kesalahan', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Situati ""last minute"" membuat bersemangat dan memunculkan potensi', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 56,
                'options' => [
                    ['text' => 'Perubahan adalah musuh', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Perubahan adalah semangat hidup', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 57,
                'options' => [
                    ['text' => 'Hidup harus sudah diatur dari awal', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Hidup seharusnya mengalir sesuai kondisi', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 58,
                'options' => [
                    ['text' => 'Daftar dan checklist adalah panduan penting', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Daftar dan checklist adalah tugas dan beban', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 59,
                'options' => [
                    ['text' => 'Puas ketika mampu menjalankan semuanya sesuai rencana', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Puas ketika mampu beradaptasi dengan momentum yang terjadi', 'pole' => 'P'] // [cite: 5]
                ]
            ],
            [
                'dichotomy' => 'JP',
                'order' => 60,
                'options' => [
                    ['text' => 'Bertindak sesuai apa yang sudah direncanakan', 'pole' => 'J'], // [cite: 10]
                    ['text' => 'Bertindak sesuai situasi dan kondisi yang terjadi saat itu', 'pole' => 'P'] // [cite: 5]
                ]
            ],
        ];

        foreach ($mbtiQuestions as $questionData) {
            $question = Question::create([
                'test_id' => $testId,
                'question_text' => 'Pilih pernyataan yang paling menggambarkan diri Anda:',
                'question_order' => $questionData['order'],
                'mbti_dichotomy' => $questionData['dichotomy']
            ]);

            foreach ($questionData['options'] as $index => $option) {
                QuestionOption::create([
                    'question_id' => $question->question_id,
                    'option_text' => $option['text'],
                    'mbti_pole_represented' => $option['pole'],
                    'display_order' => $index + 1
                ]);
            }
        }
    }
}