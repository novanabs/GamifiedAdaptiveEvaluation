<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Classes;
use App\Models\Question;
use App\Models\Subject;
use App\Models\TeacherClasses;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class guruController extends Controller
{
    public function dashboardGuru()
    {
        $user = Auth::user();
        return view('guru.dashboardguru', [
            'user' => $user,
        ]);
    }

    //data siswa
    public function dataSiswa(Request $request)
    {
        $token = $request->input('token');
        $teacherId = Auth::id();

        // 1) ambil id_class yang diaampu guru (teacher_classes)
        $classIds = DB::table('teacher_classes')
            ->where('id_teacher', $teacherId)
            ->pluck('id_class')
            ->toArray();

        // ambil data kelas untuk dropdown / sidebar (hanya kelas yg diaampu)
        $kelas = DB::table('classes')
            ->whereIn('id', $classIds)
            ->get();

        $kelasTerpilih = null;

        // jika guru tidak mengampu kelas manapun -> langsung return view kosong
        if (empty($classIds)) {
            $siswa = collect([]);
            return view('guru.datasiswa', compact('kelas', 'siswa', 'kelasTerpilih', 'token'));
        }

        // 2) jika ada token, pastikan token tersebut milik salah satu kelas yang diaampu
        if ($token) {
            $kelasTerpilih = DB::table('classes')
                ->where('token', $token)
                ->whereIn('id', $classIds) // pastikan token milik kelas yg diaampu
                ->first();

            if ($kelasTerpilih) {
                // ambil siswa hanya untuk kelas yang dipilih
                $siswa = DB::table('student_classes')
                    ->join('users', 'student_classes.id_student', '=', 'users.id')
                    ->join('classes', 'student_classes.id_class', '=', 'classes.id')
                    ->select('users.id', 'users.name', 'users.email', 'classes.name as kelas')
                    ->where('student_classes.id_class', $kelasTerpilih->id)
                    ->get();

                return view('guru.datasiswa', compact('kelas', 'siswa', 'kelasTerpilih', 'token'));
            }

            // kalau token tidak valid / bukan kelas guru -> treat as no selection (lihat langkah selanjutnya)
        }

        // 3) default: ambil semua siswa yang berada di salah satu classIds (kelas yang diaampu)
        $siswa = DB::table('student_classes')
            ->join('users', 'student_classes.id_student', '=', 'users.id')
            ->join('classes', 'student_classes.id_class', '=', 'classes.id')
            ->select('users.id', 'users.name', 'users.email', 'classes.name as kelas')
            ->whereIn('student_classes.id_class', $classIds)
            ->get();

        return view('guru.datasiswa', compact('kelas', 'siswa', 'kelasTerpilih', 'token'));
    }



    public function updateSiswa(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6', // opsional
        ]);

        $dataUpdate = [
            'name' => $request->name,
            'email' => $request->email,
            'updated_at' => now(),
        ];

        // Jika password diisi, hash dulu baru update
        if ($request->filled('password')) {
            $dataUpdate['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $request->id)->update($dataUpdate);

        return redirect()->back()->with('success', 'Data siswa berhasil diperbarui!');
    }



    //export data siswa
    public function exportSiswa(Request $request)
    {
        $token = $request->query('token');

        // Jika token ADA => export kelas tertentu
        if ($token) {
            $kelas = DB::table('classes')->where('token', $token)->first();

            if (!$kelas) {
                return redirect()->route('dataSiswa')->with('error', 'Kelas tidak ditemukan.');
            }

            $siswa = DB::table('student_classes')
                ->join('users', 'student_classes.id_student', '=', 'users.id')
                ->select('users.name', 'users.email', DB::raw("'" . $kelas->name . "' as kelas"))
                ->where('student_classes.id_class', $kelas->id)
                ->get();

            $fileName = 'Data_Siswa_' . str_replace(' ', '_', $kelas->name) . '.xlsx';

        } else {
            // Jika token TIDAK ADA => export semua siswa dari semua kelas
            $siswa = DB::table('student_classes')
                ->join('users', 'student_classes.id_student', '=', 'users.id')
                ->join('classes', 'student_classes.id_class', '=', 'classes.id')
                ->select('users.name', 'users.email', 'classes.name as kelas')
                ->orderBy('classes.name')
                ->get();

            $fileName = 'Data_Semua_Siswa.xlsx';
        }

        // Buat spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'Nama Siswa');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Kelas');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        // Isi data
        $row = 2;
        foreach ($siswa as $data) {
            $sheet->setCellValue('A' . $row, $data->name);
            $sheet->setCellValue('B' . $row, $data->email);
            $sheet->setCellValue('C' . $row, $data->kelas);
            $row++;
        }

        // Stream file
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function kelasGuru()
    {
        $idGuru = Auth::id();

        // Ambil kelas yang diajar guru ini
        $kelas = DB::table('classes')
            ->join('teacher_classes', 'classes.id', '=', 'teacher_classes.id_class')
            ->where('teacher_classes.id_teacher', $idGuru)
            ->select('classes.*')
            ->distinct()
            ->get();

        // Susun relasi manual
        $dataKelas = $kelas->map(function ($k) {
            $guru = DB::table('users')
                ->join('teacher_classes', 'users.id', '=', 'teacher_classes.id_teacher')
                ->where('teacher_classes.id_class', $k->id)
                ->pluck('users.name');

            $subjects = DB::table('subject')
                ->where('id_class', $k->id)
                ->pluck('name');

            $topics = DB::table('topics')
                ->join('subject', 'topics.id_subject', '=', 'subject.id')
                ->where('subject.id_class', $k->id)
                ->pluck('topics.title');

            $activities = DB::table('activities')
                ->join('topics', 'activities.id_topic', '=', 'topics.id')
                ->join('subject', 'topics.id_subject', '=', 'subject.id')
                ->where('subject.id_class', $k->id)
                ->pluck('activities.title');

            return (object) [
                'kelas' => $k,
                'guru' => $guru,
                'subjects' => $subjects,
                'topics' => $topics,
                'activities' => $activities,
            ];
        });
        $grades = [1, 2, 3, 4];

        return view('guru.datakelas', compact('dataKelas', 'grades'));
    }


    public function tambahKelas(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|in:SD,MI,SMP,MTs,SMA,SMK,MA,PT',
            'grade' => 'required|string|max:10',
            'semester' => 'required|in:odd,even',
            'description' => 'nullable|string',
        ]);

        $token = strtoupper(Str::random(8));

        $kelas = Classes::create([
            'name' => $request->name,
            'description' => $request->description,
            'level' => $request->level,
            'grade' => $request->grade,
            'semester' => $request->semester,
            'token' => $token,
            'created_by' => Auth::id(),
        ]);

        TeacherClasses::create([
            'id_teacher' => Auth::id(),
            'id_class' => $kelas->id,
        ]);

        return redirect()->back()->with('success', 'Kelas baru berhasil dibuat dengan token: ' . $token);
    }


    public function gabungKelas(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $kelas = Classes::where('token', $request->token)->first();

        if (!$kelas) {
            return redirect()->back()->with('error', 'Token kelas tidak ditemukan.');
        }

        // Cegah guru bergabung dua kali
        $sudahGabung = TeacherClasses::where('id_teacher', Auth::id())
            ->where('id_class', $kelas->id)
            ->exists();

        if ($sudahGabung) {
            return redirect()->back()->with('info', 'Anda sudah tergabung di kelas ini.');
        }

        TeacherClasses::create([
            'id_teacher' => Auth::id(),
            'id_class' => $kelas->id,
        ]);

        return redirect()->back()->with('success', 'Berhasil bergabung ke kelas: ' . $kelas->name);
    }
    public function updateKelas(Request $request, $id)
    {
        $kelas = Classes::findOrFail($id);

        // cek otorisasi: pastikan guru ini mengajar di kelas tersebut
        $isTeacher = TeacherClasses::where('id_class', $id)
            ->where('id_teacher', Auth::id())
            ->exists();

        if (!$isTeacher) {
            return redirect()->back()->with('error', 'Anda tidak berwenang mengubah kelas ini.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|in:SD,MI,SMP,MTs,SMA,SMK,MA,PT',
            'grade' => 'required|string|max:10',
            'semester' => 'required|in:odd,even',
            'description' => 'nullable|string',
        ]);

        $kelas->update([
            'name' => $request->name,
            'description' => $request->description,
            'level' => $request->level,
            'grade' => $request->grade,
            'semester' => $request->semester,
        ]);

        return redirect()->back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function hapusKelas(Request $request, $id)
    {
        $kelas = Classes::findOrFail($id);

        // cek otorisasi
        $isTeacher = TeacherClasses::where('id_class', $id)
            ->where('id_teacher', Auth::id())
            ->exists();

        if (!$isTeacher) {
            return redirect()->back()->with('error', 'Anda tidak berwenang menghapus kelas ini.');
        }

        // hapus relasi teacher_classes dulu
        TeacherClasses::where('id_class', $id)->delete();

        $kelas->delete();

        return redirect()->back()->with('success', 'Kelas berhasil dihapus.');
    }

    //manajemen subject
    public function dataSubject()
    {
        $idGuru = Auth::id();

        // Ambil semua kelas yang diajar guru
        $kelas = DB::table('classes')
            ->join('teacher_classes', 'classes.id', '=', 'teacher_classes.id_class')
            ->where('teacher_classes.id_teacher', $idGuru)
            ->select('classes.*')
            ->get();

        // Kelompokkan subject berdasarkan kelas, sambil mengambil creator_name
        $data = $kelas->map(function ($k) {

            // tambahkan representasi human-readable untuk semester
            $semesterHuman = ($k->semester === 'odd') ? 'Ganjil' : (($k->semester === 'even') ? 'Genap' : $k->semester);

            $subjects = DB::table('subject')
                ->leftJoin('users', 'subject.created_by', '=', 'users.id')
                ->where('subject.id_class', $k->id)
                ->select(
                    'subject.*',
                    'users.name as creator_name'
                )
                ->orderBy('subject.name', 'asc')
                ->get();

            return (object) [
                'kelas' => (object) array_merge((array) $k, ['semester_human' => $semesterHuman]),
                'subjects' => $subjects
            ];
        });

        return view('guru.datasubject', compact('data'));
    }



    public function tambahSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_class' => 'required|integer|exists:classes,id'
        ]);

        Subject::create([
            'name' => $request->name,
            'id_class' => $request->id_class,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('guru.dataSubject')->with('success', 'Mata Pelajaran berhasil ditambahkan!');
    }

    // Edit subject (sekalian ganti kelas)
    public function updateSubject(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_class' => 'required|integer|exists:classes,id'
        ]);

        $subject = Subject::findOrFail($id);

        $subject->update([
            'name' => $request->name,
            'id_class' => $request->id_class,
        ]);

        return redirect()->route('guru.dataSubject')->with('success', 'Mata Pelajaran berhasil diperbarui!');
    }

    // Hapus subject
    public function hapusSubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return redirect()->route('guru.dataSubject')->with('success', 'Mata Pelajaran berhasil dihapus!');
    }

    // 🟦 Tampilkan semua topik berdasarkan subject
    public function tampilanTopik()
    {
        $idGuru = Auth::id();

        // 1. Ambil ID kelas yang diikuti guru
        $kelasIds = DB::table('teacher_classes')
            ->where('id_teacher', $idGuru)
            ->pluck('id_class');

        // 2. Ambil subject di kelas-kelas tersebut + topics + kelas
        $data = Subject::with(['classes', 'topics'])
            ->whereIn('id_class', $kelasIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('guru.datatopic', compact('data'));
    }


    // 🟩 Simpan topik baru
    public function simpanTopik(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'id_subject' => 'required|exists:subject,id',
        ]);

        Topic::create([
            'title' => $request->title,
            'description' => $request->description,
            'id_subject' => $request->id_subject,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Topik berhasil ditambahkan!');
    }

    public function ubahTopik(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $topik = Topic::findOrFail($id);
        $topik->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Topik berhasil diperbarui!');
    }
    public function hapusTopik($id)
    {
        $topik = Topic::findOrFail($id);
        $topik->delete();

        return back()->with('success', 'Topik berhasil dihapus!');
    }

    //menampilkan data aktivitas

    public function tampilAktivitas()
    {
        $idGuru = Auth::id();

        // 1) ambil id kelas yang diajar guru ini
        $classIds = DB::table('teacher_classes')
            ->where('id_teacher', $idGuru)
            ->pluck('id_class')
            ->toArray();

        if (empty($classIds)) {
            $rows = collect();
            $questionsMap = collect();
            return view('guru.dataaktivitas', compact('rows', 'questionsMap'));
        }

        // 2) ambil topik beserta subject + classes, tapi hanya yg subject.id_class ada di $classIds
        $topics = Topic::with(['activities', 'subject.classes'])
            ->whereHas('subject', function ($q) use ($classIds) {
                $q->whereIn('id_class', $classIds);
            })
            ->get();

        // 3) flatten activities ke collection $rows (sertakan semester & class_name)
        $rows = collect();
        foreach ($topics as $topic) {
            $subject = $topic->subject;
            $className = $subject && $subject->classes ? $subject->classes->name : null;
            $semester = $subject && $subject->classes ? $subject->classes->semester : null;

            foreach ($topic->activities as $a) {
                $rows->push((object) [
                    'id' => $a->id,
                    'title' => $a->title,
                    'deadline' => $a->deadline,
                    'addaptive' => $a->addaptive,
                    'topic_id' => $topic->id,
                    'topic_title' => $topic->title,
                    'subject_name' => $subject->name ?? null,
                    'class_name' => $className,
                    'semester' => $semester,
                    'durasi_pengerjaan' => $a->durasi_pengerjaan,
                    'created_at' => $a->created_at,
                ]);
            }
        }

        if ($rows->isEmpty()) {
            $questionsMap = collect();
            return view('guru.dataaktivitas', compact('rows', 'questionsMap'));
        }

        // 4) ambil semua id activity yang muncul di $rows
        $activityIds = $rows->pluck('id')->unique()->values()->all();

        // 5) ambil pivot activity_question untuk activity-activity ini
        $activityQuestions = DB::table('activity_question')
            ->whereIn('id_activity', $activityIds)
            ->get();

        // 6) ambil semua id_question dari pivot -> query semua Question yang terkait
        $questionIds = $activityQuestions->pluck('id_question')->unique()->filter()->values()->all();

        // jika tidak ada questionIds, buat map kosong
        if (empty($questionIds)) {
            $questionsMap = collect();
            return view('guru.dataaktivitas', compact('rows', 'questionsMap'));
        }

        // 7) ambil semua soal yang terkait (tanpa membatasi created_by)
        $questionsAll = Question::whereIn('id', $questionIds)->get()->keyBy('id');

        // 8) build questionsMap: [id_activity => Collection(Question models)]
        $questionsMap = [];
        foreach ($activityQuestions as $aq) {
            $aid = $aq->id_activity ?? null;
            $qid = $aq->id_question ?? null;
            if (!$aid || !$qid)
                continue;

            if (!isset($questionsMap[$aid])) {
                $questionsMap[$aid] = collect();
            }

            // push soal bila ada di $questionsAll
            if (isset($questionsAll[$qid])) {
                $questionsMap[$aid]->push($questionsAll[$qid]);
            }
        }

        // pastikan setiap entry adalah Collection
        foreach ($questionsMap as $k => $v) {
            $questionsMap[$k] = collect($v);
        }

        // convert ke collection agar view konsisten
        $questionsMap = collect($questionsMap);

        return view('guru.dataaktivitas', compact('rows', 'questionsMap'));
    }


    /**
     * Menyimpan aktivitas baru.
     */
    public function simpanAktivitas(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'deadline' => 'nullable|date',
            'id_topic' => 'required|exists:topics,id',
            'addaptive' => 'required|in:yes,no',
            'durasi_pengerjaan' => 'nullable|integer|min:1' // menit
        ]);

        Activity::create([
            'title' => $request->title,
            'deadline' => $request->deadline,
            'id_topic' => $request->id_topic,
            'addaptive' => $request->addaptive,
            'durasi_pengerjaan' => $request->durasi_pengerjaan ?? null,
        ]);

        return redirect()->route('guru.aktivitas.tampil')
            ->with('success', 'Aktivitas berhasil ditambahkan.');
    }

    /**
     * Mengubah data aktivitas.
     */
    public function ubahAktivitas(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'deadline' => 'nullable|date',
            'addaptive' => 'required|in:yes,no',
            'durasi_pengerjaan' => 'nullable|integer|min:1'
        ]);

        $aktivitas = Activity::findOrFail($id);

        $aktivitas->update([
            'title' => $request->title,
            'deadline' => $request->deadline,
            'addaptive' => $request->addaptive,
            'durasi_pengerjaan' => $request->durasi_pengerjaan ?? null,
        ]);

        return redirect()->route('guru.aktivitas.tampil')
            ->with('success', 'Aktivitas berhasil diperbarui.');
    }

    /**
     * Menghapus aktivitas.
     */
    public function hapusAktivitas($id)
    {
        $aktivitas = Activity::findOrFail($id);
        $aktivitas->delete(); // otomatis hapus pivot + hasil
        return redirect()
            ->route('guru.aktivitas.tampil')
            ->with('success', 'Aktivitas dan data terkait berhasil dihapus.');
    }

    //Menampilkan daftar soal
    public function tampilanSoal()
    {
        $guruId = Auth::id();

        // 1) ambil kelas yang diajar guru
        $kelasIds = DB::table('teacher_classes')
            ->where('id_teacher', $guruId)
            ->pluck('id_class')
            ->toArray();

        // jika guru tidak mengajar kelas apapun -> return view kosong
        if (empty($kelasIds)) {
            $data = collect();
            $topics = collect();
            $subjects = collect();
            return view('guru.datasoal', compact('data', 'topics', 'subjects'));
        }

        // 2) ambil subject yang masuk ke kelas tersebut (bila butuh di view)
        $subjects = DB::table('subject')
            ->whereIn('id_class', $kelasIds)
            ->select('id', 'name', 'id_class')
            ->orderBy('name')
            ->get();

        // 3) ambil topics hanya untuk subject di atas (bila butuh di view)
        $subjectIds = $subjects->pluck('id')->toArray();
        $topics = DB::table('topics')
            ->whereIn('id_subject', $subjectIds)
            ->select('id', 'title', 'id_subject')
            ->orderBy('title')
            ->get();

        // 4) ambil semua question yang terhubung ke topics pada kelas ini
        // join: question.id_topic -> topics.id -> subject.id_subject -> classes (subject.id_class)
        $questions = DB::table('question')
            ->join('topics', 'question.id_topic', '=', 'topics.id')
            ->join('subject', 'topics.id_subject', '=', 'subject.id')
            ->whereIn('subject.id_class', $kelasIds)
            ->select(
                'question.*',
                'topics.title as topic_title',
                'topics.id_subject as topic_subject_id',
                'subject.name as subject_name',
                'subject.id_class as class_id'
            )
            ->orderBy('question.created_at', 'desc')
            ->get();

        // decode json fields for view convenience
        foreach ($questions as $item) {
            $item->question = is_string($item->question) ? json_decode($item->question) : $item->question;
            $item->MC_option = $item->MC_option ? (is_string($item->MC_option) ? json_decode($item->MC_option) : $item->MC_option) : null;
            $item->SA_answer = $item->SA_answer ? (is_string($item->SA_answer) ? json_decode($item->SA_answer) : $item->SA_answer) : null;
        }

        // kirim ke view: semua soal yang topiknya berada di kelas yang diajar guru
        return view('guru.datasoal', [
            'data' => $questions,
            'topics' => $topics,
            'subjects' => $subjects
        ]);
    }


    public function editTopikSoal(Request $request, $id)
    {
        $guruId = Auth::id();

        // temukan soal (pastikan owner adalah guru)
        $question = Question::where('id', $id)->where('created_by', $guruId)->first();
        if (!$question) {
            return response()->json(['success' => false, 'message' => 'Soal tidak ditemukan atau bukan milik Anda.'], 404);
        }

        $payload = $request->json()->all();

        // jika id_topic disediakan -> pakai itu
        if (!empty($payload['id_topic'])) {
            $idTopic = intval($payload['id_topic']);
            $topic = Topic::find($idTopic);
            if (!$topic) {
                return response()->json(['success' => false, 'message' => 'Topik tidak ditemukan.'], 404);
            }
            $question->id_topic = $topic->id;
            $question->save();

            return response()->json(['success' => true, 'id_topic' => $topic->id, 'title' => $topic->title]);
        }

        // jika membuat topik baru -> butuh id_subject + topic_title
        if (!empty($payload['topic_title']) && !empty($payload['id_subject'])) {
            $title = trim($payload['topic_title']);
            $idSubject = intval($payload['id_subject']);

            // verifikasi subject berada di kelas guru
            $subject = DB::table('subject')->where('id', $idSubject)->first();
            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Mata pelajaran tidak ditemukan.'], 404);
            }
            // pastikan guru punya akses ke kelas subject tersebut
            $kelasId = $subject->id_class;
            $teaches = DB::table('teacher_classes')
                ->where('id_teacher', $guruId)
                ->where('id_class', $kelasId)
                ->exists();
            if (!$teaches) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak untuk membuat topik pada mata pelajaran ini.'], 403);
            }

            // buat topik (firstOrCreate berdasarkan title + subject)
            $topic = Topic::firstOrCreate(
                ['title' => $title, 'id_subject' => $idSubject],
                ['created_by' => $guruId]
            );

            $question->id_topic = $topic->id;
            $question->save();

            return response()->json(['success' => true, 'id_topic' => $topic->id, 'title' => $topic->title]);
        }

        return response()->json(['success' => false, 'message' => 'Payload tidak valid.'], 422);
    }



    // 🔹 Halaman tambah soal
    // tampilkan form tambah soal + topics yang relevan
    public function tambahSoal()
    {
        $guruId = Auth::id();

        // ambil kelas yang diajar
        $kelasIds = DB::table('teacher_classes')
            ->where('id_teacher', $guruId)
            ->pluck('id_class')
            ->toArray();

        // ambil subject dalam kelas tersebut
        $subjectIds = DB::table('subject')
            ->whereIn('id_class', $kelasIds)
            ->pluck('id')
            ->toArray();

        // ambil topics untuk subject di atas
        $topics = DB::table('topics')
            ->whereIn('id_subject', $subjectIds)
            ->select('id', 'title', 'id_subject')
            ->orderBy('title')
            ->get();

        // juga kalau mau bisa kirim subjects (opsional)
        $subjects = DB::table('subject')->whereIn('id', $subjectIds)->select('id', 'name', 'id_class')->get();

        return view('guru.tambahsoal', compact('topics', 'subjects'));
    }

    // simpan soal baru
    public function simpanSoal(Request $request)
    {
        $request->validate([
            'type' => 'required|in:MultipleChoice,ShortAnswer',
            'question_text' => 'required|string',
            'difficulty' => 'nullable|in:mudah,sedang,sulit',
            'id_topic' => 'nullable|exists:topics,id'
        ]);

        $questionData = [
            'text' => $request->question_text,
            'URL' => $request->question_url ?? null,
        ];

        // jika user upload file gambar, kamu bisa simpan file dan set URL di $questionData['URL']
        if ($request->hasFile('question_image')) {
            $f = $request->file('question_image');
            $path = $f->store('public/question_images'); // sesuaikan disk
            $questionData['URL'] = \Storage::url($path);
        }

        $mcOption = null;
        $saAnswer = null;
        $mcAnswer = null;

        if ($request->type === 'MultipleChoice') {
            $options = [];
            $texts = $request->input('option_text', []);
            $urls = $request->input('option_url', []);
            // option_image file handling jika diperlukan (saat upload file, butuh loop melalui request->file('option_image'))
            foreach ($texts as $index => $text) {
                $label = chr(97 + $index); // a,b,c,d,e
                $options[] = [
                    $label => [
                        'teks' => $text ?? '',
                        'url' => $urls[$index] ?? null
                    ]
                ];
            }
            $mcOption = !empty($options) ? json_encode($options) : null;
            $mcAnswer = $request->mc_answer ?? null;
        } else {
            $saAnswer = $request->input('sa_answer') ? json_encode(array_values(array_filter($request->input('sa_answer')))) : null;
        }

        $question = Question::create([
            'type' => $request->type,
            'question' => json_encode($questionData),
            'MC_option' => $mcOption,
            'SA_answer' => $saAnswer,
            'MC_answer' => $mcAnswer,
            'difficulty' => $request->difficulty ?? 'sedang',
            'id_topic' => $request->id_topic ?? null,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Soal berhasil disimpan!');

    }
    // 🔹 Edit soal
    public function editSoal($id)
    {
        $data = Question::findOrFail($id);

        // ambil kelas yang diajar guru saat ini
        $teacherId = Auth::id();

        // ambil id_class dari teacher_classes (raw query atau model)
        $classIds = DB::table('teacher_classes')
            ->where('id_teacher', $teacherId)
            ->pluck('id_class')
            ->toArray();

        // ambil subjects yang ada di kelas2 tersebut
        $subjectIds = DB::table('subject')
            ->whereIn('id_class', $classIds)
            ->pluck('id')
            ->toArray();

        // ambil topics yang terkait subjects di atas (hanya topik untuk subject yg guru ajar)
        $topics = Topic::whereIn('id_subject', $subjectIds)
            ->orderBy('title')
            ->get();

        return view('guru.editsoal', compact('data', 'topics'));
    }

    /**
     * Update soal — handling file uploads & opsi dengan aman
     */
    public function updateSoal(Request $request, $id)
    {
        $data = Question::findOrFail($id);

        // optional: authorization check
        // if ($data->created_by !== Auth::id()) {
        //     return redirect()->route('tampilanSoal')->with('error','Tidak berhak mengedit soal ini.');
        // }

        // Basic validation
        $rules = [
            'question_text' => 'required|string',
            'question_url' => 'nullable|url',
            'difficulty' => 'nullable|in:mudah,sedang,sulit',
            'id_topic' => 'nullable|exists:topics,id',
        ];

        // Jika tipe multiple choice, validasi minimal struktur
        if ($data->type === 'MultipleChoice') {
            // option_text[] mungkin dikirim; mc_answer wajib
            $rules['option_text'] = 'required|array|min:1';
            $rules['option_text.*'] = 'nullable|string';
            $rules['option_url.*'] = 'nullable|url';
            // option_image.* akan kita proses secara manual (file)
            $rules['mc_answer'] = 'required|in:a,b,c,d,e';
        } else { // ShortAnswer
            $rules['sa_answer'] = 'nullable|array';
            $rules['sa_answer.*'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        // Build question JSON
        $questionData = [
            'text' => $request->question_text,
            'URL' => $request->question_url ?? null,
        ];

        // Handle file upload for question image (override question_url if file provided)
        if ($request->hasFile('question_image')) {
            $file = $request->file('question_image');
            // simpan ke storage/app/public/question_images
            $path = $file->store('public/question_images');
            $url = Storage::url($path);
            $questionData['URL'] = $url;
        }

        $mcOption = null;
        $saAnswer = null;
        $mcAnswer = null;

        if ($data->type === 'MultipleChoice') {
            $texts = $request->input('option_text', []);           // array of strings (may be less than 5)
            $urls = $request->input('option_url', []);            // array of urls
            $files = $request->file('option_image', []);           // array of UploadedFile or null

            // Ensure we have 5 elements (a-e). If input has less, fill with empty strings
            $labels = ['a', 'b', 'c', 'd', 'e'];
            $options = [];

            for ($i = 0; $i < 5; $i++) {
                $label = $labels[$i];
                $text = isset($texts[$i]) ? trim((string) $texts[$i]) : '';
                $optUrl = isset($urls[$i]) ? $urls[$i] : null;

                // If user uploaded a file for this option, store it and override optUrl
                if (isset($files[$i]) && $files[$i] && is_uploaded_file($files[$i]->getPathname())) {
                    $p = $files[$i]->store('public/option_images');
                    $optUrl = Storage::url($p);
                }

                // Keep consistent structure: label => ['teks'=>..., 'url'=>...]
                $options[] = [
                    $label => [
                        'teks' => $text,
                        'url' => $optUrl ?: null
                    ]
                ];
            }

            $mcOption = json_encode($options);
            $mcAnswer = $request->mc_answer; // validated to be a/b/c/d/e
        } else {
            // ShortAnswer: remove empty answers and reindex
            $sa = $request->input('sa_answer', []);
            $filtered = array_values(array_filter(array_map(function ($v) {
                return is_null($v) ? null : trim((string) $v);
            }, (array) $sa), function ($v) {
                return $v !== null && $v !== '';
            }));

            $saAnswer = !empty($filtered) ? json_encode($filtered) : null;
        }

        // Update record
        $data->question = json_encode($questionData);
        $data->MC_option = $mcOption;
        $data->SA_answer = $saAnswer;
        $data->MC_answer = $mcAnswer;
        $data->difficulty = $request->difficulty ?? $data->difficulty;
        $data->id_topic = $request->id_topic ?? $data->id_topic;
        $data->save();

        return back()->with('success', 'Soal berhasil diedit!');

    }

    // 🔹 Hapus soal
    public function hapusSoal($id)
    {
        $data = Question::findOrFail($id);
        $data->delete();
        return redirect()->route('tampilanSoal')->with('success', '🗑️ Soal berhasil dihapus!');
    }

}
