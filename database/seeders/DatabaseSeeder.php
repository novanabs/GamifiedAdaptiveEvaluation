<?php

namespace Database\Seeders;

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
        // === 1️⃣ Buat Guru ===
        $guru1 = User::create([
            'id_other' => 'NIP001',
            'type_id_other' => 'NIP',
            'nama' => 'Guru Informatika',
            'email' => 'guru1@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        $guru2 = User::create([
            'id_other' => 'NIP002',
            'type_id_other' => 'NIP',
            'nama' => 'Guru IPA',
            'email' => 'guru2@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        // === 2️⃣ Buat Siswa ===
        $siswa1 = User::create([
            'id_other' => 'NISN001',
            'type_id_other' => 'NISN',
            'nama' => 'Siswa A',
            'email' => 'siswa1@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        $siswa2 = User::create([
            'id_other' => 'NISN002',
            'type_id_other' => 'NISN',
            'nama' => 'Siswa B',
            'email' => 'siswa2@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);



        // === 3️⃣ Buat Badge ===
        $badgeA = Badge::create(['name' => 'Badge A', 'description' => 'Pencapaian A']);
        $badgeB = Badge::create(['name' => 'Badge B', 'description' => 'Pencapaian B']);

        // Assign badge random ke siswa
        $siswaList = [$siswa1, $siswa2];
        foreach ($siswaList as $siswa) {
            UserBadge::create([
                'id_student' => $siswa->id,
                'id_badge' => rand(0, max: 1) ? $badgeA->id : $badgeB->id,
            ]);
        }

        // === 4️⃣ Buat Kelas ===
        $kelas7 = Classes::create([
            'name' => '7 SMP',
            'description' => 'Kelas 7 SMP',
            'level' => '7',
            'token' => 'KLS7TOKEN',
            'created_by' => $guru1->id,
        ]);

        $kelas8 = Classes::create([
            'name' => '8 SMP',
            'description' => 'Kelas 8 SMP',
            'level' => '8',
            'token' => 'KLS8TOKEN',
            'created_by' => $guru2->id,
        ]);

        // === 5️⃣ Assign guru ke kelas ===
        TeacherClasses::create(['id_teacher' => $guru1->id, 'id_class' => $kelas7->id]);
        TeacherClasses::create(['id_teacher' => $guru2->id, 'id_class' => $kelas8->id]);

        // === 6️⃣ Assign siswa ke guru (masing-masing 2 siswa) ===
        StudentClasses::create(['id_student' => $siswa1->id, 'id_class' => $kelas7->id]);
        StudentClasses::create(['id_student' => $siswa2->id, 'id_class' => $kelas7->id]);
        StudentClasses::create(['id_student' => $siswa1->id, 'id_class' => $kelas8->id]);
        StudentClasses::create(['id_student' => $siswa2->id, 'id_class' => $kelas8->id]);

        // === 7️⃣ Buat Subject ===
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

        // === 8️⃣ Buat Topic ===
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

        // === 9️⃣ Buat Activity untuk masing-masing subject ===
        $statuses = ['basic', 'additional', 'remedial'];

        foreach ($statuses as $status) {
            Activity::create([
                'title' => ucfirst($status) . ' Informatika',
                'status' => $status,
                'type' => 'task',
                'deadline' => now()->addDays(7),
                'id_topic' => $topicInformatika->id,
            ]);
        }

        foreach ($statuses as $status) {
            Activity::create([
                'title' => ucfirst($status) . ' IPA',
                'status' => $status,
                'type' => 'quiz',
                'deadline' => now()->addDays(7),
                'id_topic' => $topicIPA->id,
            ]);
        }

        // === 🔟 Buat Question (2 tipe: MC & SA) ===
        $questionMC = Question::create([
            'type' => 'MultipleChoice',
            'question' => json_encode([
                'text' => 'Apa fungsi utama spreadsheet?',
                'URL' => 'https://www.corporatecomplianceinsights.com/wp-content/uploads/2018/06/spreadsheet.jpg'
            ]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'Mengelola data numerik', 'url' => 'https://uc-r.github.io/public/images/analytics/descriptives/descriptive_stats_numeric_icon2.png']],
                ['b' => ['teks' => 'Membuat animasi', 'url' => 'https://idseducation.com/wp-content/uploads/2017/05/proses-pembuatan-video-animasi-1.jpg']],
                ['c' => ['teks' => 'Menulis surat', 'url' => 'https://modulkomputer.com/wp-content/uploads/2017/08/cara-membuat-kop-surat-di-microsoft-word.png']],
                ['d' => ['teks' => 'Mengedit video', 'url' => 'https://cdnpro.eraspace.com/media/mageplaza/blog/post/d/a/davinci-resolve.jpg']],
                ['e' => ['teks' => 'Mendengarkan musik', 'url' => 'https://ids.ac.id/wp-content/uploads/2022/10/imgonline-com-ua-CompressToSize-j7Cq9PF91NhVUGy-1024x554.jpg']],
            ]),
            'MC_answer' => 'a',
            'created_by' => $guru1->id,
        ]);

        $questionMC2 = Question::create([
            'type' => 'MultipleChoice',
            'question' => json_encode([
                'text' => 'File spreadsheet umumnya memiliki ekstensi apa?',
                'URL' => null
            ]),
            'MC_option' => json_encode([
                ['a' => ['teks' => 'xls', 'url' => null]],
                ['b' => ['teks' => 'txt', 'url' => null]],
                ['c' => ['teks' => 'doc', 'url' => null]],
                ['d' => ['teks' => 'ppt', 'url' => null]],
                ['e' => ['teks' => 'jpg', 'url' => null]],
            ]),
            'MC_answer' => 'a',
            'created_by' => $guru1->id,
        ]);

        $questionSA = Question::create([
            'type' => 'ShortAnswer',
            'question' => json_encode([
                'text' => 'Jelaskan apa yang dimaksud dengan gerak lurus beraturan.',
                'URL' => 'https://asset.kompas.com/data/photo/2020/10/05/5f7ab6af559d3.jpg'
            ]),
            'SA_answer' => json_encode([
                'konstan',
                'tetap',
                'beraturan'
            ]),
            'created_by' => $guru2->id,
        ]);

        $questionSA2 = Question::create([
            'type' => 'ShortAnswer',
            'question' => json_encode([
                'text' => 'Sebutkan satuan kecepatan dalam SI!',
                'URL' => null
            ]),
            'SA_answer' => json_encode(['m/s', 'km/jam', 'meter/detik']),
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
            ActivityQuestion::create([
                'id_activity' => $activity->id,
                'id_question' => $questionMC->id,
            ]);
            ActivityQuestion::create([
                'id_activity' => $activity->id,
                'id_question' => $questionMC2->id,
            ]);
        }
        foreach ($activitiesIPA as $activity) {
            ActivityQuestion::create([
                'id_activity' => $activity->id,
                'id_question' => $questionSA->id,
            ]);
            ActivityQuestion::create([
                'id_activity' => $activity->id,
                'id_question' => $questionSA2->id,
            ]);
        }

    }
}
