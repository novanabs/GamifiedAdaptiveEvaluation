<?php

namespace Database\Seeders;

use App\Models\ActivityResult;
use App\Models\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Activity;
use App\Models\ActivityQuestion;
use App\Models\Question;
use App\Models\UserBadge;
use App\Models\Badge;
use App\Models\StudentClasses;
use App\Models\TeacherClasses;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === 1️⃣ Guru ===
        $guru1 = User::create([
            'id_other' => 'NIP001',
            'type_id_other' => 'NIP',
            'name' => 'Guru Informatika',
            'email' => 'guru1@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        $guru2 = User::create([
            'id_other' => 'NIP002',
            'type_id_other' => 'NIP',
            'name' => 'Guru IPA',
            'email' => 'guru2@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        // === 2️⃣ Siswa ===
        $siswa1 = User::create([
            'id_other' => 'NISN001',
            'type_id_other' => 'NISN',
            'name' => 'Wahyu',
            'email' => 'Wahyu@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        $siswa2 = User::create([
            'id_other' => 'NISN002',
            'type_id_other' => 'NISN',
            'name' => 'Norman',
            'email' => 'norman@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // === 3️⃣ Badge ===
        $badgeA = Badge::create([
            'name' => 'Fastest Students',
            'description' => 'Pencapaian Siswa Paling Cepat Selesai Mengerjakan Satu Aktivitas',
            'path_icon' => 'img/1.png'
        ]);

        $badgeB = Badge::create([
            'name' => 'Top 3 Students',
            'description' => 'Pencapaian Siswa menjadi peringkat 3 terbaik dalam leaderboard',
            'path_icon' => 'img/2.png'
        ]);

        $badgeC = Badge::create([
            'name' => 'Smartest Students',
            'description' => 'Pencapaian Siswa dengan menjawab benar semua dalam satu aktivitas',
            'path_icon' => 'img/3.png'
        ]);


        $badges = [$badgeA->id, $badgeB->id, $badgeC->id];

        foreach ([$siswa1, $siswa2] as $siswa) {
            UserBadge::create([
                'id_student' => $siswa->id,
                'id_badge' => $badges[array_rand($badges)],
            ]);
        }


        // === 4️⃣ Kelas ===
        $kelas7 = Classes::create([
            'name' => '7 SMP',
            'description' => 'Kelas 7 SMP',
            'level' => 'SMP',
            'grade' => '1',
            'semester' => 'odd',
            'token' => 'KLS7TOKEN',
            'created_by' => $guru1->id,
        ]);

        $kelas8 = Classes::create([
            'name' => '8 SMP',
            'description' => 'Kelas 8 SMP',
            'level' => 'SMP',
            'grade' => '2',
            'semester' => 'even',
            'token' => 'KLS8TOKEN',
            'created_by' => $guru2->id,
        ]);

        TeacherClasses::create(['id_teacher' => $guru1->id, 'id_class' => $kelas7->id]);
        TeacherClasses::create(['id_teacher' => $guru2->id, 'id_class' => $kelas8->id]);

        StudentClasses::insert([
            ['id_student' => $siswa1->id, 'id_class' => $kelas7->id],
            ['id_student' => $siswa2->id, 'id_class' => $kelas7->id],
            ['id_student' => $siswa1->id, 'id_class' => $kelas8->id],
            ['id_student' => $siswa2->id, 'id_class' => $kelas8->id],
        ]);

        // === 5️⃣ Subject ===
        $subjectInformatika = Subject::create([
            'name' => 'Informatika',
            'id_class' => $kelas7->id,
            'created_by' => $guru1->id,
        ]);

        $subjectIPA = Subject::create([
            'name' => 'IPA',
            'id_class' => $kelas8->id,
            'created_by' => $guru2->id,
        ]);

        // === 6️⃣ Topic ===
        $topicInformatika = Topic::create([
            'title' => 'Kelola Data dengan Spreadsheet',
            'description' => 'Pengelolaan data menggunakan spreadsheet.',
            'id_subject' => $subjectInformatika->id,
            'created_by' => $guru1->id,
        ]);

        $topicIPA = Topic::create([
            'title' => 'Gerak',
            'description' => 'Mempelajari konsep gerak dalam kehidupan sehari-hari.',
            'id_subject' => $subjectIPA->id,
            'created_by' => $guru2->id,
        ]);

        // === 7️⃣ Activity ===
        $statuses = ['basic', 'additional', 'remedial'];
        $labels = ['Kuis 1', 'Kuis 2', 'Kuis 3'];

        // ===== INFORMATIKA =====
        foreach ($statuses as $index => $status) {
            Activity::create([
                'title' => $labels[$index] . ' Informatika',
                'status' => $status,
                'type' => 'task',
                'deadline' => now()->addDays(7),
                'jumlah_soal' => 5,
                'durasi_pengerjaan' => 5,
                'id_topic' => $topicInformatika->id,
                'addaptive' => 'yes'
            ]);
        }

        // ===== IPA =====
        foreach ($statuses as $index => $status) {
            Activity::create([
                'title' => $labels[$index] . ' IPA',
                'status' => $status,
                'type' => 'quiz',
                'deadline' => now()->addDays(7),
                'jumlah_soal' => 5,
                'durasi_pengerjaan' => 5,
                'id_topic' => $topicIPA->id,
                'addaptive' => 'yes'
            ]);
        }

        $informatikaQuestions = [];

        // MUDAH 1 (MC)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Apa fungsi utama spreadsheet?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'Mengelola data numerik', 'url' => null]],
                ['b' => ['teks' => 'Mengedit video', 'url' => null]],
                ['c' => ['teks' => 'Menulis surat', 'url' => null]],
                ['d' => ['teks' => 'Mendengarkan musik', 'url' => null]],
                ['e' => ['teks' => 'Membuat animasi', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'mudah',
            'created_by' => $guru1->id,
        ]);

        // MUDAH 2 (MC)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Aplikasi spreadsheet adalah?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'Microsoft Excel', 'url' => null]],
                ['b' => ['teks' => 'Microsoft Word', 'url' => null]],
                ['c' => ['teks' => 'PowerPoint', 'url' => null]],
                ['d' => ['teks' => 'Photoshop', 'url' => null]],
                ['e' => ['teks' => 'CorelDraw', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'mudah',
            'created_by' => $guru1->id,
        ]);

        // MUDAH 3 (SA)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Sebutkan satu contoh aplikasi spreadsheet!', 'URL' => null]),
            'SA_answer' => json_encode(['excel', 'google sheets', 'libreoffice calc']),
            'difficulty' => 'mudah',
            'created_by' => $guru1->id,
        ]);

        // SEDANG 1 (MC)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Perpotongan baris dan kolom disebut?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'Cell', 'url' => null]],
                ['b' => ['teks' => 'Sheet', 'url' => null]],
                ['c' => ['teks' => 'Workbook', 'url' => null]],
                ['d' => ['teks' => 'Range', 'url' => null]],
                ['e' => ['teks' => 'File', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'sedang',
            'created_by' => $guru1->id,
        ]);

        // SEDANG 2 (MC)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Fungsi SUM pada spreadsheet digunakan untuk?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'Menjumlahkan data', 'url' => null]],
                ['b' => ['teks' => 'Mengurutkan data', 'url' => null]],
                ['c' => ['teks' => 'Menyaring data', 'url' => null]],
                ['d' => ['teks' => 'Menghapus data', 'url' => null]],
                ['e' => ['teks' => 'Mencari data', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'sedang',
            'created_by' => $guru1->id,
        ]);

        // SEDANG 3 (SA)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Apa fungsi grafik dalam spreadsheet?', 'URL' => null]),
            'SA_answer' => json_encode(['visualisasi data', 'menyajikan data', 'grafik data']),
            'difficulty' => 'sedang',
            'created_by' => $guru1->id,
        ]);

        // SEDANG 4 (SA)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Apa kegunaan fitur sort?', 'URL' => null]),
            'SA_answer' => json_encode(['mengurutkan data', 'sorting data', 'urut data']),
            'difficulty' => 'sedang',
            'created_by' => $guru1->id,
        ]);

        // SEDANG 5 (SA)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Apa yang dimaksud dengan worksheet?', 'URL' => null]),
            'SA_answer' => json_encode(['lembar kerja', 'sheet', 'worksheet']),
            'difficulty' => 'sedang',
            'created_by' => $guru1->id,
        ]);

        // SULIT 1 (SA)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Jelaskan perbedaan worksheet dan workbook!', 'URL' => null]),
            'SA_answer' => json_encode([
                'worksheet lembar kerja workbook kumpulan worksheet',
                'worksheet bagian workbook',
                'workbook berisi worksheet'
            ]),
            'difficulty' => 'sulit',
            'created_by' => $guru1->id,
        ]);

        // SULIT 2 (SA)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Jelaskan kegunaan fitur filter dalam pengolahan data!', 'URL' => null]),
            'SA_answer' => json_encode([
                'menyaring data sesuai kriteria',
                'menampilkan data tertentu',
                'filter data'
            ]),
            'difficulty' => 'sulit',
            'created_by' => $guru1->id,
        ]);

        // SULIT 3 (MC)
        $informatikaQuestions[] = Question::create([
            'id_topic' => '1',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Rumus yang benar untuk menghitung rata-rata A1 sampai A5 adalah?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => '=AVERAGE(A1:A5)', 'url' => null]],
                ['b' => ['teks' => '=SUM(A1:A5)', 'url' => null]],
                ['c' => ['teks' => '=COUNT(A1:A5)', 'url' => null]],
                ['d' => ['teks' => '=MAX(A1:A5)', 'url' => null]],
                ['e' => ['teks' => '=MIN(A1:A5)', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'sulit',
            'created_by' => $guru1->id,
        ]);

        $ipaQuestions = [];

        // MUDAH 1 (MC)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Gerak lurus beraturan adalah gerak dengan?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'Kecepatan tetap', 'url' => null]],
                ['b' => ['teks' => 'Percepatan berubah', 'url' => null]],
                ['c' => ['teks' => 'Lintasan melengkung', 'url' => null]],
                ['d' => ['teks' => 'Arah berubah', 'url' => null]],
                ['e' => ['teks' => 'Kecepatan bertambah', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'mudah',
            'created_by' => $guru2->id,
        ]);

        // MUDAH 2 (MC)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Satuan kecepatan dalam SI adalah?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'm/s', 'url' => null]],
                ['b' => ['teks' => 'km', 'url' => null]],
                ['c' => ['teks' => 'detik', 'url' => null]],
                ['d' => ['teks' => 'meter', 'url' => null]],
                ['e' => ['teks' => 'jam', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'mudah',
            'created_by' => $guru2->id,
        ]);

        // MUDAH 3 (SA)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Sebutkan satu contoh gerak lurus dalam kehidupan sehari-hari!', 'URL' => null]),
            'SA_answer' => json_encode(['mobil', 'sepeda', 'kereta']),
            'difficulty' => 'mudah',
            'created_by' => $guru2->id,
        ]);

        // SEDANG 1 (MC)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Rumus kecepatan adalah?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'v = s / t', 'url' => null]],
                ['b' => ['teks' => 'v = t / s', 'url' => null]],
                ['c' => ['teks' => 's = v / t', 'url' => null]],
                ['d' => ['teks' => 't = s × v', 'url' => null]],
                ['e' => ['teks' => 'v = s × t', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'sedang',
            'created_by' => $guru2->id,
        ]);

        // SEDANG 2 (MC)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Alat untuk mengukur waktu adalah?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'Stopwatch', 'url' => null]],
                ['b' => ['teks' => 'Termometer', 'url' => null]],
                ['c' => ['teks' => 'Mistar', 'url' => null]],
                ['d' => ['teks' => 'Neraca', 'url' => null]],
                ['e' => ['teks' => 'Barometer', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'sedang',
            'created_by' => $guru2->id,
        ]);

        // SEDANG 3 (SA)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Apa yang dimaksud dengan kecepatan?', 'URL' => null]),
            'SA_answer' => json_encode([
                'jarak per waktu',
                'perpindahan per waktu',
                's dibagi t'
            ]),
            'difficulty' => 'sedang',
            'created_by' => $guru2->id,
        ]);

        // SEDANG 4 (SA)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Apa yang dimaksud dengan jarak?', 'URL' => null]),
            'SA_answer' => json_encode([
                'panjang lintasan',
                'lintasan yang ditempuh',
                'jarak tempuh'
            ]),
            'difficulty' => 'sedang',
            'created_by' => $guru2->id,
        ]);

        // SEDANG 5 (SA)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Apa yang dimaksud dengan waktu dalam gerak?', 'URL' => null]),
            'SA_answer' => json_encode([
                'lama gerak',
                'selang waktu',
                'durasi'
            ]),
            'difficulty' => 'sedang',
            'created_by' => $guru2->id,
        ]);

        // SULIT 1 (SA)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Jelaskan apa yang dimaksud dengan gerak lurus beraturan!', 'URL' => null]),
            'SA_answer' => json_encode([
                'kecepatan tetap lintasan lurus',
                'kecepatan konstan',
                'gerak lurus dengan kecepatan tetap'
            ]),
            'difficulty' => 'sulit',
            'created_by' => $guru2->id,
        ]);

        // SULIT 2 (SA)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'ShortAnswer',
            'question' => json_encode(['text' => 'Jelaskan perbedaan jarak dan perpindahan!', 'URL' => null]),
            'SA_answer' => json_encode([
                'jarak lintasan perpindahan posisi',
                'jarak total perpindahan lurus',
                'jarak dan arah'
            ]),
            'difficulty' => 'sulit',
            'created_by' => $guru2->id,
        ]);

        // SULIT 3 (MC)
        $ipaQuestions[] = Question::create([
            'id_topic' => '2',
            'type' => 'MultipleChoice',
            'question' => json_encode(['text' => 'Jika sebuah benda menempuh jarak 100 m dalam 20 s, maka kecepatannya adalah?', 'URL' => null]),
            'MC_option' => json_encode([
                ['a' => ['teks' => '5 m/s', 'url' => null]],
                ['b' => ['teks' => '2 m/s', 'url' => null]],
                ['c' => ['teks' => '10 m/s', 'url' => null]],
                ['d' => ['teks' => '20 m/s', 'url' => null]],
                ['e' => ['teks' => '100 m/s', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'difficulty' => 'sulit',
            'created_by' => $guru2->id,
        ]);


        $activitiesInformatika = Activity::whereHas(
            'topic.subject',
            fn($q) =>
            $q->where('name', 'Informatika')
        )->get();

        $activitiesIPA = Activity::whereHas(
            'topic.subject',
            fn($q) =>
            $q->where('name', 'IPA')
        )->get();

        foreach ($activitiesInformatika as $activity) {
            foreach ($informatikaQuestions as $question) {
                ActivityQuestion::create([
                    'id_activity' => $activity->id,
                    'id_question' => $question->id,
                ]);
            }
        }
        // === 🔟 Nilai Siswa ===
        $allStudents = [$siswa1, $siswa2];
        $allActivities = Activity::all();

        foreach ($allStudents as $student) {
            foreach ($allActivities as $activity) {
                $result = rand(40, 100);
                $status = $result < 70 ? 'Remedial' : 'Pass';
                $realPoin = $result < 60 ? 10 : 20;

                ActivityResult::create([
                    'id_user' => $student->id,
                    'id_activity' => $activity->id,
                    'result_status' => $status,
                    'result' => $result,
                    'real_poin' => $realPoin,
                    'bonus_poin' => rand(0, 5),
                ]);
            }
        }
        Settings::create([
            'name' => 'soal_mudah',
            'value' => 10
        ]);
        Settings::create([
            'name' => 'soal_sedang',
            'value' => 20
        ]);
        Settings::create([
            'name' => 'soal_sulit',
            'value' => 30
        ]);
        Settings::create([
            'name' => 'kkm',
            'value' => 70
        ]);
    }
}
