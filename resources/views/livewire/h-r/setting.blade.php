<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            @if (session()->has('message'))
                <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
             @if (session()->has('error'))
                <div class="alert alert-danger text-white alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header p-3 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="font-weight-bolder mb-0">Pengaturan Posisi Jabatan</h5>
                            <p class="text-sm text-secondary mb-0">Tambah, ubah, atau hapus posisi jabatan yang tersedia untuk analisis.</p>
                        </div>
                        <button wire:click="create()" class="btn bg-gradient-primary mb-0">
                            <i class="material-icons text-sm">add</i>&nbsp;&nbsp;Tambah Posisi Baru
                        </button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Posisi</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Profil RIASEC Ideal</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Profil MBTI Ideal</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jobPositions as $position)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $position->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ Str::limit($position->description, 50) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if(is_array($position->ideal_riasec_profile))
                                            @foreach($position->ideal_riasec_profile as $code)
                                                <span class="badge badge-sm bg-gradient-info">{{ $code }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                         @if(is_array($position->ideal_mbti_profile))
                                            @foreach($position->ideal_mbti_profile as $type)
                                                <span class="badge badge-sm bg-gradient-warning">{{ $type }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="align-middle text-right">
                                        <button wire:click="edit({{ $position->id }})" class="btn btn-link text-dark px-3 mb-0" href="javascript:;"><i class="material-icons text-sm me-2">edit</i>Edit</button>
                                        <button wire:click="delete({{ $position->id }})" wire:confirm="Anda yakin ingin menghapus posisi ini?" class="btn btn-link text-danger px-3 mb-0" href="javascript:;"><i class="material-icons text-sm me-2">delete</i>Hapus</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center p-3">Belum ada posisi jabatan yang ditambahkan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="p-3">
                        {{ $jobPositions->links() }}
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header p-3">
                    <h5 class="mb-0">📚 Panduan Profil Ideal untuk Posisi IT</h5>
                    <p class="text-sm text-secondary mb-0">Rekomendasi berdasarkan karakteristik kepribadian MBTI dan minat RIASEC</p>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionJobProfiles">
                        @php
                        $jobProfiles = [
                            [
                                'title' => 'Software Developer / Programmer',
                                'mbti' => ['INTJ', 'INTP', 'ENTJ', 'ENTP', 'ISTJ', 'ISTP'],
                                'riasec' => ['RIA', 'RIS', 'IRA', 'IRC'],
                                'description' => 'Cocok untuk individu yang analitis, logis, dan menikmati pemecahan masalah kompleks melalui kode.'
                            ],
                            [
                                'title' => 'UI/UX Designer',
                                'mbti' => ['ENFP', 'INFP', 'ENFJ', 'INFJ', 'ISFP'],
                                'riasec' => ['ARI', 'AIR', 'AIS', 'ASI'],
                                'description' => 'Ideal untuk orang kreatif yang memahami kebutuhan pengguna dan dapat menerjemahkannya ke desain yang intuitif.'
                            ],
                            [
                                'title' => 'Data Analyst / Data Scientist',
                                'mbti' => ['ISTJ', 'INTJ', 'INTP', 'ESTJ'],
                                'riasec' => ['IRA', 'IRC', 'CIR', 'ICR'],
                                'description' => 'Sesuai untuk pemikir analitis yang suka menggali insight dari data dan membuat keputusan berbasis fakta.'
                            ],
                            [
                                'title' => 'Project Manager',
                                'mbti' => ['ENTJ', 'ENFJ', 'ESTJ', 'ESFJ'],
                                'riasec' => ['ESC', 'ECS', 'SEC', 'SCE'],
                                'description' => 'Sempurna untuk pemimpin alami yang terorganisir dan mampu mengkoordinasikan tim dengan efektif.'
                            ],
                            [
                                'title' => 'System/Network Administrator',
                                'mbti' => ['ISTJ', 'ISTP', 'INTJ', 'INTP'],
                                'riasec' => ['RIC', 'RCI', 'IRC', 'CRI'],
                                'description' => 'Cocok untuk individu yang teliti, metodis, dan menikmati memelihara sistem agar berjalan optimal.'
                            ],
                            [
                                'title' => 'Quality Assurance Engineer',
                                'mbti' => ['ISTJ', 'ISFJ', 'ESTJ', 'INTJ'],
                                'riasec' => ['CIR', 'CRI', 'ICR', 'RCI'],
                                'description' => 'Ideal untuk orang yang detail-oriented dan memiliki passion untuk memastikan kualitas produk.'
                            ],
                            [
                                'title' => 'Business Analyst',
                                'mbti' => ['ENTJ', 'ESTJ', 'INTJ', 'ISTJ'],
                                'riasec' => ['CES', 'CSE', 'ECS', 'ICS'],
                                'description' => 'Sesuai untuk pemikir strategis yang dapat menjembatani kebutuhan bisnis dengan solusi teknologi.'
                            ],
                            [
                                'title' => 'Cybersecurity Analyst',
                                'mbti' => ['INTJ', 'ISTJ', 'INTP', 'ENTJ'],
                                'riasec' => ['RIC', 'IRC', 'ICR', 'CRI'],
                                'description' => 'Sempurna untuk individu yang analitis, waspada, dan tertarik pada keamanan sistem.'
                            ]
                        ];
                        @endphp
                        @foreach($jobProfiles as $index => $profile)
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }} bg-gray-100" type="button"
                                         data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                                         aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                    <i class="material-icons text-sm me-2">work</i>
                                    <strong>{{ $profile['title'] }}</strong>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                  aria-labelledby="heading{{ $index }}" data-bs-parent="#accordionJobProfiles">
                                <div class="accordion-body">
                                    <p class="text-sm mb-3">{{ $profile['description'] }}</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-xs text-uppercase mb-2">
                                                <i class="material-icons text-sm align-middle">psychology</i> Tipe MBTI Ideal:
                                            </h6>
                                            <div class="mb-3">
                                                @foreach($profile['mbti'] as $mbti)
                                                    <span class="badge bg-gradient-warning me-1 mb-1">{{ $mbti }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-xs text-uppercase mb-2">
                                                <i class="material-icons text-sm align-middle">interests</i> Kode RIASEC Ideal:
                                            </h6>
                                            <div class="mb-3">
                                                @foreach($profile['riasec'] as $riasec)
                                                    <span class="badge bg-gradient-info me-1 mb-1">{{ $riasec }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-light text-dark text-xs mb-0" role="alert">
                                        <strong>💡 Tips:</strong> Gunakan kombinasi ini sebagai panduan saat membuat posisi baru.
                                         Anda dapat menyesuaikan sesuai kebutuhan spesifik perusahaan.
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header p-3">
                            <h5 class="mb-0">🎯 Panduan Lengkap MBTI untuk HR Professional</h5>
                            <p class="text-sm text-secondary mb-0">Memahami tipe kepribadian untuk penempatan posisi yang tepat</p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="accordion" id="accordionMBTI">
                                        @php
                                        $mbtiGroups = [
                                            [
                                                'title' => 'Analysts (NT) - Pemikir Strategis',
                                                'color' => 'primary',
                                                'icon' => 'psychology',
                                                'overview' => 'Kelompok ini dikenal sebagai pemikir strategis yang sangat analitis dan logis. Mereka cenderung fokus pada konsep abstrak, teori, dan sistem yang kompleks.',
                                                'ideal_roles' => ['Software Engineering', 'System Architecture', 'Data Science', 'Research & Development', 'Strategic Planning'],
                                                'types' => [
                                                    [
                                                        'code' => 'INTJ',
                                                        'name' => 'Architect',
                                                        'traits' => 'Strategis, inovatif, terorganisir, visioner',
                                                        'strengths' => ['Perencanaan jangka panjang', 'Pemecahan masalah kompleks', 'Berpikir sistematis', 'Independen'],
                                                        'work_style' => 'Bekerja mandiri, fokus pada hasil, membutuhkan tantangan intelektual',
                                                        'best_for' => ['Technical Lead', 'Product Manager', 'System Analyst', 'Research Scientist'],
                                                        'avoid' => ['Pekerjaan rutin repetitif', 'Micromanagement', 'Terlalu banyak meeting'],
                                                        'team_role' => 'Visioner strategis yang memberikan arah dan solusi inovatif'
                                                    ],
                                                    [
                                                        'code' => 'INTP',
                                                        'name' => 'Thinker',
                                                        'traits' => 'Analitis, objektif, teoretis, fleksibel',
                                                        'strengths' => ['Analisis mendalam', 'Kreativitas teknis', 'Adaptabilitas', 'Pemikiran logis'],
                                                        'work_style' => 'Membutuhkan kebebasan eksplorasi, tidak suka deadline ketat',
                                                        'best_for' => ['Software Developer', 'Data Scientist', 'Research Engineer', 'Technical Writer'],
                                                        'avoid' => ['Pekerjaan dengan struktur ketat', 'Presentasi berulang', 'Administrasi'],
                                                        'team_role' => 'Problem solver yang mencari solusi kreatif dan inovatif'
                                                    ],
                                                    [
                                                        'code' => 'ENTJ',
                                                        'name' => 'Commander',
                                                        'traits' => 'Pemimpin alami, efisien, tegas, ambisius',
                                                        'strengths' => ['Kepemimpinan', 'Pengambilan keputusan', 'Organisasi', 'Visi strategis'],
                                                        'work_style' => 'Mengambil inisiatif, menyukai tantangan, berorientasi hasil',
                                                        'best_for' => ['Project Manager', 'Team Lead', 'Product Manager', 'CTO'],
                                                        'avoid' => ['Pekerjaan individual tanpa dampak', 'Rutinitas tanpa variasi'],
                                                        'team_role' => 'Pemimpin yang mengarahkan tim menuju tujuan strategis'
                                                    ],
                                                    [
                                                        'code' => 'ENTP',
                                                        'name' => 'Debater',
                                                        'traits' => 'Inovatif, fleksibel, argumentatif, kreatif',
                                                        'strengths' => ['Brainstorming', 'Adaptabilitas', 'Networking', 'Inovasi'],
                                                        'work_style' => 'Menyukai variasi, diskusi terbuka, tantangan baru',
                                                        'best_for' => ['Solution Architect', 'Product Owner', 'Technical Consultant', 'Innovation Manager'],
                                                        'avoid' => ['Detail implementasi', 'Pekerjaan monoton', 'Proses birokratis'],
                                                        'team_role' => 'Katalis ide yang mendorong inovasi dan perubahan'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'title' => 'Diplomats (NF) - Komunikator Empatik',
                                                'color' => 'success',
                                                'icon' => 'groups',
                                                'overview' => 'Kelompok ini adalah komunikator alami yang peduli dengan pengembangan manusia dan harmoni tim. Mereka memiliki kemampuan interpersonal yang kuat.',
                                                'ideal_roles' => ['UX Design', 'Product Management', 'Training & Development', 'Team Leadership', 'User Research'],
                                                'types' => [
                                                    [
                                                        'code' => 'INFJ',
                                                        'name' => 'Advocate',
                                                        'traits' => 'Idealis, terorganisir, altruistik, intuitif',
                                                        'strengths' => ['Empati', 'Visi jangka panjang', 'Konsistensi', 'Motivasi intrinsik'],
                                                        'work_style' => 'Bekerja dengan purpose, membutuhkan makna dalam pekerjaan',
                                                        'best_for' => ['UX Designer', 'Product Manager', 'Training Specialist', 'Technical Writer'],
                                                        'avoid' => ['Lingkungan konflik tinggi', 'Tekanan waktu ekstrem', 'Pekerjaan tanpa dampak'],
                                                        'team_role' => 'Mentor yang membantu pengembangan tim dan user experience'
                                                    ],
                                                    [
                                                        'code' => 'INFP',
                                                        'name' => 'Mediator',
                                                        'traits' => 'Empatik, kreatif, idealis, fleksibel',
                                                        'strengths' => ['Kreativitas', 'Adaptabilitas', 'Pemahaman user', 'Autentisitas'],
                                                        'work_style' => 'Membutuhkan otonomi, bekerja dengan nilai personal',
                                                        'best_for' => ['UI/UX Designer', 'Content Creator', 'User Researcher', 'Frontend Developer'],
                                                        'avoid' => ['Kritik berlebihan', 'Lingkungan kompetitif', 'Pekerjaan bertentangan nilai'],
                                                        'team_role' => 'Kreator yang menghasilkan solusi user-centric'
                                                    ],
                                                    [
                                                        'code' => 'ENFJ',
                                                        'name' => 'Protagonist',
                                                        'traits' => 'Karismatik, inspiratif, altruistik, terorganisir',
                                                        'strengths' => ['Leadership', 'Komunikasi', 'Pengembangan tim', 'Motivasi'],
                                                        'work_style' => 'Fokus pada pengembangan orang, komunikasi terbuka',
                                                        'best_for' => ['Scrum Master', 'Team Lead', 'Product Owner', 'Training Manager'],
                                                        'avoid' => ['Pekerjaan isolasi', 'Konflik berkepanjangan', 'Lingkungan negatif'],
                                                        'team_role' => 'Pemimpin yang menginspirasi dan mengembangkan anggota tim'
                                                    ],
                                                    [
                                                        'code' => 'ENFP',
                                                        'name' => 'Campaigner',
                                                        'traits' => 'Antusias, kreatif, sosial, spontan',
                                                        'strengths' => ['Kreativitas', 'Networking', 'Motivasi', 'Fleksibilitas'],
                                                        'work_style' => 'Menyukai variasi, kolaborasi, dan kebebasan bereksperimen',
                                                        'best_for' => ['Product Manager', 'UX Designer', 'Developer Relations', 'Innovation Lead'],
                                                        'avoid' => ['Pekerjaan detail berulang', 'Isolasi', 'Struktur kaku'],
                                                        'team_role' => 'Energizer yang membawa semangat dan ide segar'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'title' => 'Sentinels (SJ) - Stabilizer Organisasi',
                                                'color' => 'warning',
                                                'icon' => 'shield',
                                                'overview' => 'Kelompok ini adalah tulang punggung organisasi yang dapat diandalkan. Mereka fokus pada stabilitas, proses, dan konsistensi dalam eksekusi.',
                                                'ideal_roles' => ['Quality Assurance', 'Operations', 'Database Administration', 'Security', 'Process Management'],
                                                'types' => [
                                                    [
                                                        'code' => 'ISTJ',
                                                        'name' => 'Logistician',
                                                        'traits' => 'Praktis, bertanggung jawab, teliti, konsisten',
                                                        'strengths' => ['Keandalan', 'Perhatian detail', 'Konsistensi', 'Perencanaan'],
                                                        'work_style' => 'Metodis, mengikuti prosedur, bekerja mandiri',
                                                        'best_for' => ['QA Engineer', 'Database Admin', 'Backend Developer', 'System Admin'],
                                                        'avoid' => ['Perubahan mendadak', 'Ambiguitas', 'Pekerjaan tanpa struktur'],
                                                        'team_role' => 'Penjaga kualitas yang memastikan standar dan konsistensi'
                                                    ],
                                                    [
                                                        'code' => 'ISFJ',
                                                        'name' => 'Defender',
                                                        'traits' => 'Protektif, hangat, teliti, suportif',
                                                        'strengths' => ['Dukungan tim', 'Ketelitian', 'Loyalitas', 'Empati'],
                                                        'work_style' => 'Kolaboratif, membantu rekan, fokus pada harmony',
                                                        'best_for' => ['QA Engineer', 'Technical Support', 'Documentation', 'User Support'],
                                                        'avoid' => ['Konflik tinggi', 'Kritik berlebihan', 'Kompetisi agresif'],
                                                        'team_role' => 'Supporter yang membantu kelancaran operasional tim'
                                                    ],
                                                    [
                                                        'code' => 'ESTJ',
                                                        'name' => 'Executive',
                                                        'traits' => 'Terorganisir, praktis, tegas, efisien',
                                                        'strengths' => ['Manajemen', 'Organisasi', 'Implementasi', 'Kepemimpinan'],
                                                        'work_style' => 'Berorientasi hasil, struktur jelas, manajemen proses',
                                                        'best_for' => ['Project Manager', 'Operations Manager', 'DevOps Lead', 'QA Manager'],
                                                        'avoid' => ['Ambiguitas', 'Proses tidak jelas', 'Perubahan terlalu cepat'],
                                                        'team_role' => 'Eksekutor yang memastikan proyek berjalan sesuai rencana'
                                                    ],
                                                    [
                                                        'code' => 'ESFJ',
                                                        'name' => 'Consul',
                                                        'traits' => 'Peduli, kooperatif, harmonis, suportif',
                                                        'strengths' => ['Kolaborasi', 'Komunikasi', 'Dukungan tim', 'Organisasi'],
                                                        'work_style' => 'Team-oriented, komunikasi terbuka, fokus pada harmoni',
                                                        'best_for' => ['Scrum Master', 'Team Coordinator', 'User Support', 'Training Specialist'],
                                                        'avoid' => ['Pekerjaan isolasi', 'Konflik berkepanjangan', 'Kritik berlebihan'],
                                                        'team_role' => 'Fasilitator yang menjaga kohesi dan produktivitas tim'
                                                    ]
                                                ]
                                            ],
                                            [
                                                'title' => 'Explorers (SP) - Adaptif & Praktis',
                                                'color' => 'info',
                                                'icon' => 'explore',
                                                'overview' => 'Kelompok ini adalah problem solver praktis yang adaptif dan responsif. Mereka unggul dalam situasi yang membutuhkan fleksibilitas dan tindakan cepat.',
                                                'ideal_roles' => ['DevOps', 'Frontend Development', 'Technical Support', 'Prototyping', 'Crisis Management'],
                                                'types' => [
                                                    [
                                                        'code' => 'ISTP',
                                                        'name' => 'Virtuoso',
                                                        'traits' => 'Praktis, tenang, analitis, mandiri',
                                                        'strengths' => ['Problem solving', 'Adaptabilitas', 'Efisiensi', 'Kemandirian'],
                                                        'work_style' => 'Hands-on, learning by doing, bekerja mandiri',
                                                        'best_for' => ['DevOps Engineer', 'Backend Developer', 'System Troubleshooter', 'Technical Specialist'],
                                                        'avoid' => ['Teori berlebihan', 'Micromanagement', 'Pekerjaan tanpa variasi'],
                                                        'team_role' => 'Troubleshooter yang menyelesaikan masalah teknis dengan cepat'
                                                    ],
                                                    [
                                                        'code' => 'ISFP',
                                                        'name' => 'Adventurer',
                                                        'traits' => 'Artistik, fleksibel, menawan, sensitif',
                                                        'strengths' => ['Kreativitas', 'Adaptabilitas', 'Estetika', 'Empati'],
                                                        'work_style' => 'Ekspresif, fleksibel, bekerja dengan inspirasi',
                                                        'best_for' => ['UI Designer', 'Frontend Developer', 'UX Researcher', 'Creative Developer'],
                                                        'avoid' => ['Kritik berlebihan', 'Struktur kaku', 'Konflik tinggi'],
                                                        'team_role' => 'Kreator yang menghasilkan solusi estetis dan user-friendly'
                                                    ],
                                                    [
                                                        'code' => 'ESTP',
                                                        'name' => 'Entrepreneur',
                                                        'traits' => 'Energik, perseptif, spontan, pragmatis',
                                                        'strengths' => ['Adaptabilitas', 'Komunikasi', 'Crisis management', 'Networking'],
                                                        'work_style' => 'Action-oriented, kolaboratif, responsif terhadap perubahan',
                                                        'best_for' => ['DevOps Engineer', 'Technical Sales', 'Support Engineer', 'Implementation Specialist'],
                                                        'avoid' => ['Pekerjaan teoretis', 'Rutinitas panjang', 'Isolasi'],
                                                        'team_role' => 'Responder yang cepat mengatasi masalah dan implementasi'
                                                    ],
                                                    [
                                                        'code' => 'ESFP',
                                                        'name' => 'Entertainer',
                                                        'traits' => 'Spontan, energik, antusias, people-oriented',
                                                        'strengths' => ['Komunikasi', 'Motivasi', 'Fleksibilitas', 'Kolaborasi'],
                                                        'work_style' => 'Energik, people-focused, menyukai variasi',
                                                        'best_for' => ['User Support', 'Frontend Developer', 'Developer Relations', 'Training Specialist'],
                                                        'avoid' => ['Pekerjaan isolasi', 'Kritik berlebihan', 'Struktur kaku'],
                                                        'team_role' => 'Motivator yang membawa energi positif dan kolaborasi'
                                                    ]
                                                ]
                                            ]
                                        ];
                                        @endphp
                                        
                                        @foreach($mbtiGroups as $groupIndex => $group)
                                        <div class="accordion-item border-0 mb-3">
                                            <h2 class="accordion-header" id="headingGroup{{ $groupIndex }}">
                                                <button class="accordion-button {{ $groupIndex > 0 ? 'collapsed' : '' }} bg-gradient-{{ $group['color'] }} text-white rounded-3" 
                                                        type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#collapseGroup{{ $groupIndex }}" 
                                                        aria-expanded="{{ $groupIndex === 0 ? 'true' : 'false' }}">
                                                    <i class="material-icons text-sm ms-4 me-2">{{ $group['icon'] }}</i>
                                                    <strong>{{ $group['title'] }}</strong>
                                                </button>
                                            </h2>
                                            <div id="collapseGroup{{ $groupIndex }}" class="accordion-collapse collapse {{ $groupIndex === 0 ? 'show' : '' }}" 
                                                data-bs-parent="#accordionMBTI">
                                                <div class="accordion-body p-4">
                                                    <!-- Group Overview -->
                                                    <div class="alert alert-{{ $group['color'] }} mb-4 rounded-3">
                                                        <h6 class="alert-heading mb-2 text-white">
                                                            <i class="material-icons text-sm align-middle me-1">info</i>
                                                            Overview Kelompok
                                                        </h6>
                                                        <p class="mb-2 text-white">{{ $group['overview'] }}</p>
                                                        <hr class="my-2">
                                                        <small class="text-white">
                                                            <strong>Ideal untuk posisi:</strong>
                                                            @foreach($group['ideal_roles'] as $role)
                                                                <span class="badge bg-light text-dark me-1">{{ $role }}</span>
                                                            @endforeach
                                                        </small>
                                                    </div>
                                                    
                                                    <!-- Individual Types -->
                                                    <div class="row">
                                                        @foreach($group['types'] as $type)
                                                        <div class="col-md-6 mb-4">
                                                            <div class="card border-{{ $group['color'] }} h-100 rounded-3">
                                                                <div class="card-header bg-gradient-{{ $group['color'] }} text-white rounded-top-3">
                                                                    <h6 class="mb-0 text-white">
                                                                        <span class="badge bg-light text-dark me-2">{{ $type['code'] }}</span>
                                                                        {{ $type['name'] }}
                                                                    </h6>
                                                                    <small class="opacity-8 text-white">{{ $type['traits'] }}</small>
                                                                </div>
                                                                <div class="card-body p-3">
                                                                    <!-- Strengths -->
                                                                    <div class="mb-3">
                                                                        <h6 class="text-xs text-uppercase mb-2">
                                                                            <i class="material-icons text-sm align-middle text-success">thumb_up</i>
                                                                            Kekuatan Utama
                                                                        </h6>
                                                                        <div class="d-flex flex-wrap gap-1">
                                                                            @foreach($type['strengths'] as $strength)
                                                                                <span class="badge bg-success bg-gradient text-white text-xs">{{ $strength }}</span>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Work Style -->
                                                                    <div class="mb-3">
                                                                        <h6 class="text-xs text-uppercase mb-2">
                                                                            <i class="material-icons text-sm align-middle text-info">work</i>
                                                                            Gaya Kerja
                                                                        </h6>
                                                                        <p class="text-xs mb-0">{{ $type['work_style'] }}</p>
                                                                    </div>
                                                                    
                                                                    <!-- Best For -->
                                                                    <div class="mb-3">
                                                                        <h6 class="text-xs text-uppercase mb-2">
                                                                            <i class="material-icons text-sm align-middle text-primary">star</i>
                                                                            Posisi Terbaik
                                                                        </h6>
                                                                        <div class="d-flex flex-wrap gap-1">
                                                                            @foreach($type['best_for'] as $position)
                                                                                <span class="badge bg-primary bg-gradient text-white text-xs">{{ $position }}</span>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Avoid -->
                                                                    <div class="mb-3">
                                                                        <h6 class="text-xs text-uppercase mb-2">
                                                                            <i class="material-icons text-sm align-middle text-danger">block</i>
                                                                            Hindari
                                                                        </h6>
                                                                        <div class="d-flex flex-wrap gap-1">
                                                                            @foreach($type['avoid'] as $avoid)
                                                                                <span class="badge bg-danger bg-gradient text-white text-xs">{{ $avoid }}</span>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Team Role -->
                                                                    <div class="alert alert-light mb-0 rounded-3">
                                                                        <h6 class="text-xs text-uppercase mb-2">
                                                                            <i class="material-icons text-sm align-middle">group</i>
                                                                            Peran dalam Tim
                                                                        </h6>
                                                                        <p class="text-xs mb-0">{{ $type['team_role'] }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <!-- HR Tips Sidebar -->
                                <div class="col-md-4">
                                    <div class="card sticky-top rounded-3">
                                        <div class="card-header bg-gradient-dark text-white rounded-top-3">
                                            <h6 class="mb-0 text-white">
                                                <i class="material-icons text-sm align-middle me-2">lightbulb</i>
                                                Tips untuk HR
                                            </h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <!-- Interview Tips -->
                                            <div class="mb-4">
                                                <h6 class="text-primary mb-2">
                                                    <i class="material-icons text-sm align-middle me-1">quiz</i>
                                                    Tips Interview
                                                </h6>
                                                <ul class="list-unstyled text-sm">
                                                    <li class="mb-2">
                                                        <strong>Introverts (I):</strong> Berikan waktu untuk berpikir, hindari tekanan berlebihan
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>Extroverts (E):</strong> Ajukan pertanyaan yang memungkinkan mereka berbicara
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>Sensing (S):</strong> Fokus pada pengalaman konkret dan detail
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>Intuition (N):</strong> Eksplorasi visi dan ide-ide besar
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>Thinking (T):</strong> Tanyakan tentang logika dan analisis
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>Feeling (F):</strong> Eksplorasi nilai dan dampak pada orang
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>Judging (J):</strong> Tanyakan tentang perencanaan dan organisasi
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>Perceiving (P):</strong> Eksplorasi fleksibilitas dan adaptabilitas
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <!-- Team Composition -->
                                            <div class="mb-4">
                                                <h6 class="text-success mb-2">
                                                    <i class="material-icons text-sm align-middle me-1">groups</i>
                                                    Komposisi Tim Ideal
                                                </h6>
                                                <div class="alert alert-light text-sm rounded-3 mb-2">
                                                    <strong>Tim Development:</strong><br>
                                                    • 1-2 NT (Arsitektur & Strategi)<br>
                                                    • 2-3 SJ (Implementasi & QA)<br>
                                                    • 1-2 SP (Problem Solving)<br>
                                                    • 1 NF (UX & Komunikasi)
                                                </div>
                                                <div class="alert alert-light text-sm rounded-3">
                                                    <strong>Tim Leadership:</strong><br>
                                                    • ENTJ/ESTJ (Eksekusi)<br>
                                                    • ENFJ/ESFJ (People Management)<br>
                                                    • INTJ/ISTJ (Perencanaan)<br>
                                                    • ENFP/ENTP (Inovasi)
                                                </div>
                                            </div>
                                            
                                            <!-- Red Flags -->
                                            <div class="mb-4">
                                                <h6 class="text-danger mb-2">
                                                    <i class="material-icons text-sm align-middle me-1">warning</i>
                                                    Red Flags
                                                </h6>
                                                <ul class="list-unstyled text-sm">
                                                    <li class="mb-2">
                                                        <i class="material-icons text-xs text-danger me-1">close</i>
                                                        Terlalu banyak NT tanpa SJ = Kurang eksekusi
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="material-icons text-xs text-danger me-1">close</i>
                                                        Terlalu banyak SJ tanpa NT = Kurang inovasi
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="material-icons text-xs text-danger me-1">close</i>
                                                        Tim tanpa NF = Kurang user empathy
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="material-icons text-xs text-danger me-1">close</i>
                                                        Semua Introverts = Kurang komunikasi
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="material-icons text-xs text-danger me-1">close</i>
                                                        Semua Perceivers = Kurang struktur
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <!-- Career Development -->
                                            <div class="mb-4">
                                                <h6 class="text-info mb-2">
                                                    <i class="material-icons text-sm align-middle me-1">trending_up</i>
                                                    Pengembangan Karir
                                                </h6>
                                                <ul class="list-unstyled text-sm">
                                                    <li class="mb-2">
                                                        <strong>NT:</strong> Beri tantangan kompleks, learning opportunities
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>NF:</strong> Fokus pada impact dan pengembangan people skills
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>SJ:</strong> Jalur karir yang jelas, skill improvement
                                                    </li>
                                                    <li class="mb-2">
                                                        <strong>SP:</strong> Variasi projek, hands-on experience
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <!-- Performance Management -->
                                            <div>
                                                <h6 class="text-warning mb-2">
                                                    <i class="material-icons text-sm align-middle me-1">assessment</i>
                                                    Performance Management
                                                </h6>
                                                <div class="alert alert-light text-xs rounded-3">
                                                    <strong>Feedback Style:</strong><br>
                                                    • <strong>T types:</strong> Direct, fact-based<br>
                                                    • <strong>F types:</strong> Supportive, personal<br>
                                                    • <strong>J types:</strong> Structured, goal-oriented<br>
                                                    • <strong>P types:</strong> Flexible, collaborative
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- RIASEC Information -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header p-3">
                            <h6 class="mb-0">Keterangan Dimensi Minat RIASEC</h6>
                        </div>
                        <div class="card-body p-3">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-gradient-info me-2">R</span>
                                        <div>
                                            <strong>Realistic (Realistis)</strong>
                                            <p class="text-sm mb-0">Menyukai aktivitas praktis, bekerja dengan alat, mesin, atau hewan. Cenderung langsung, jujur, dan praktis.</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-gradient-warning me-2">I</span>
                                        <div>
                                            <strong>Investigative (Investigatif)</strong>
                                            <p class="text-sm mb-0">Suka memecahkan masalah kompleks, menganalisis data, dan melakukan penelitian. Cenderung analitis, intelektual, dan suka belajar.</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-gradient-success me-2">A</span>
                                        <div>
                                            <strong>Artistic (Artistik)</strong>
                                            <p class="text-sm mb-0">Menyukai aktivitas kreatif seperti seni, drama, musik, atau menulis. Cenderung imajinatif, ekspresif, dan independen.</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-gradient-primary me-2">S</span>
                                        <div>
                                            <strong>Social (Sosial)</strong>
                                            <p class="text-sm mb-0">Suka membantu, mengajar, atau melayani orang lain. Cenderung kooperatif, suportif, dan memiliki kemampuan interpersonal yang baik.</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-gradient-danger me-2">E</span>
                                        <div>
                                            <strong>Enterprising (Wirausaha)</strong>
                                            <p class="text-sm mb-0">Menyukai memimpin, mempengaruhi, atau menjual. Cenderung energik, ambisius, dan percaya diri.</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-gradient-secondary me-2">C</span>
                                        <div>
                                            <strong>Conventional (Konvensional)</strong>
                                            <p class="text-sm mb-0">Suka bekerja dengan data, angka, atau prosedur yang jelas. Cenderung terorganisir, teliti, dan mengikuti aturan.</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header p-3">
                            <h6 class="mb-0">🔍 Panduan Praktis Assessment</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="alert alert-primary mb-3">
                                <h6 class="text-white mb-2">
                                    <i class="material-icons text-sm align-middle me-1">psychology</i>
                                    Cara Mengidentifikasi MBTI
                                </h6>
                                <ul class="text-sm text-white mb-0">
                                    <li><strong>E vs I:</strong> Perhatikan cara mereka memproses informasi (verbal vs internal)</li>
                                    <li><strong>S vs N:</strong> Lihat fokus mereka (detail konkret vs gambaran besar)</li>
                                    <li><strong>T vs F:</strong> Amati cara pengambilan keputusan (logika vs nilai)</li>
                                    <li><strong>J vs P:</strong> Observasi preferensi struktur (terorganisir vs fleksibel)</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-info mb-3">
                                <h6 class="text-white mb-2">
                                    <i class="material-icons text-sm align-middle me-1">interests</i>
                                    Cara Mengidentifikasi RIASEC
                                </h6>
                                <ul class="text-sm text-white mb-0">
                                    <li>Tanyakan tentang hobi dan aktivitas yang mereka nikmati</li>
                                    <li>Eksplor pengalaman kerja atau proyek yang paling memuaskan</li>
                                    <li>Perhatikan jenis masalah yang mereka sukai untuk dipecahkan</li>
                                    <li>Amati lingkungan kerja yang mereka preferensikan</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-success mb-0">
                                <h6 class="text-white mb-2">
                                    <i class="material-icons text-sm align-middle me-1">verified</i>
                                    Best Practices
                                </h6>
                                <ul class="text-sm text-white mb-0">
                                    <li>Jangan bergantung 100% pada assessment - gunakan sebagai panduan</li>
                                    <li>Kombinasikan dengan observasi perilaku dan performa</li>
                                    <li>Pertimbangkan soft skills dan cultural fit</li>
                                    <li>Berikan ruang untuk pertumbuhan dan adaptasi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isModalOpen)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? 'Edit Posisi Jabatan' : 'Tambah Posisi Jabatan Baru' }}</h5>
                    <button wire:click="closeModal" type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="store">
                        <div class="mb-3">
                            <label>Nama Posisi Jabatan</label>
                            <div class="input-group input-group-outline">
                                <input wire:model="name" type="text" class="form-control" placeholder="cth: Software Engineer">
                            </div>
                            @error('name') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi</label>
                            <div class="input-group input-group-outline">
                                <textarea wire:model="description" class="form-control" rows="3" placeholder="Jelaskan secara singkat tentang posisi ini"></textarea>
                            </div>
                             @error('description') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                         <div class="mb-3">
                            <label>Profil RIASEC Ideal</label>
                            <div class="input-group input-group-outline">
                                <input wire:model="ideal_riasec_profile" type="text" class="form-control" placeholder="cth: RIA,RIS,IRC (pisahkan dengan koma)">
                            </div>
                            <p class="text-xs text-secondary mb-0">Masukkan 3 huruf kode Holland, pisahkan dengan koma jika lebih dari satu.</p>
                            @error('ideal_riasec_profile') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label>Profil MBTI Ideal</label>
                            <div class="input-group input-group-outline">
                                <input wire:model="ideal_mbti_profile" type="text" class="form-control" placeholder="cth: INTJ,INTP,ENTJ (pisahkan dengan koma)">
                            </div>
                            <p class="text-xs text-secondary mb-0">Masukkan 4 huruf tipe MBTI, pisahkan dengan koma jika lebih dari satu.</p>
                             @error('ideal_mbti_profile') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal" type="button" class="btn bg-gradient-secondary">Batal</button>
                    <button wire:click.prevent="store" type="button" class="btn bg-gradient-primary">{{ $isEditMode ? 'Simpan Perubahan' : 'Tambahkan' }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>