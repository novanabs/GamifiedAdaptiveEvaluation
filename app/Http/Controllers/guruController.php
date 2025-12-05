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

        // Ambil semua kelas milik guru login
        $kelas = DB::table('classes')
            ->where('created_by', Auth::id())
            ->get();

        $kelasTerpilih = null;

        // 🟦 Default: tampilkan semua siswa dari semua kelas guru
        $siswa = DB::table('student_classes')
            ->join('users', 'student_classes.id_student', '=', 'users.id')
            ->join('classes', 'student_classes.id_class', '=', 'classes.id')
            ->select('users.id', 'users.name', 'users.email', 'classes.name as kelas')
            ->where('classes.created_by', Auth::id())
            ->get();

        // 🟩 Jika memilih token → filter siswa sesuai kelas
        if ($token) {

            $kelasTerpilih = DB::table('classes')
                ->where('token', $token)
                ->where('created_by', Auth::id())
                ->first();

            if ($kelasTerpilih) {
                $siswa = DB::table('student_classes')
                    ->join('users', 'student_classes.id_student', '=', 'users.id')
                    ->join('classes', 'student_classes.id_class', '=', 'classes.id')
                    ->select('users.id', 'users.name', 'users.email', 'classes.name as kelas')
                    ->where('student_classes.id_class', $kelasTerpilih->id)
                    ->get();
            }
        }

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

        // Kelompokkan subject berdasarkan kelas
        $data = $kelas->map(function ($k) {
            $subjects = DB::table('subject')->where('id_class', $k->id)->get();
            return (object) [
                'kelas' => $k,
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

    // Edit subject
    public function updateSubject(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $subject = Subject::findOrFail($id);
        $subject->update(['name' => $request->name]);
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
        $data = Subject::with('classes', 'topics')
            ->where('created_by', Auth::id())
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

    // 🟨 Perbarui topik
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

    // 🟥 Hapus topik
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

        // ambil topik beserta activities dan subject->classes
        $topics = Topic::with(['activities', 'subject.classes'])
            ->where('created_by', $idGuru)
            ->get();

        // soal yang dibuat guru ini (Eloquent Collection)
        $questions = Question::where('created_by', $idGuru)->get();

        // pivot table: activity_question (kumpulan baris {id_activity, id_question})
        $activityQuestions = DB::table('activity_question')->get();

        // flatten activities ke $rows
        $rows = collect();
        foreach ($topics as $topic) {
            $className = $topic->subject && $topic->subject->classes ? $topic->subject->classes->name : null;
            foreach ($topic->activities as $a) {
                $rows->push((object) [
                    'id' => $a->id,
                    'title' => $a->title,
                    'deadline' => $a->deadline,
                    'addaptive' => $a->addaptive,
                    'topic_id' => $topic->id,
                    'topic_title' => $topic->title,
                    'subject_name' => $topic->subject->name ?? null,
                    'class_name' => $className,
                    'durasi_pengerjaan' => $a->durasi_pengerjaan,
                ]);

            }
        }

        // build questions map: [ id_activity => Collection(Question models) ]
        $questionsMap = [];
        $qById = $questions->keyBy('id'); // keyed by question id for fast lookup

        foreach ($activityQuestions as $aq) {
            // $aq bisa object stdClass dari query builder
            $aid = $aq->id_activity ?? null;
            $qid = $aq->id_question ?? null;
            if (!$aid || !$qid)
                continue;

            if (!isset($questionsMap[$aid]))
                $questionsMap[$aid] = collect();
            if (isset($qById[$qid])) {
                $questionsMap[$aid]->push($qById[$qid]);
            }
        }

        // pastikan setiap entry adalah Collection (jika perlu)
        foreach ($questionsMap as $k => $v)
            $questionsMap[$k] = collect($v);

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


    //manajemen soal (bank soal)
    //Menampilkan daftar soal
    public function tampilanSoal()
    {
        $data = Question::with('topic')
            ->where('created_by', Auth::id())
            ->get();

        foreach ($data as $item) {
            $item->question = json_decode($item->question);
            $item->MC_option = $item->MC_option ? json_decode($item->MC_option) : null;
            $item->SA_answer = $item->SA_answer ? json_decode($item->SA_answer) : null;
        }

        // daftar topik
        $topics = Topic::all();

        return view('guru.datasoal', compact('data', 'topics'));
    }



    public function editTopikSoal(Request $request, $id)
    {
        $request->validate([
            'id_topic' => 'required|exists:topics,id'
        ]);

        $question = Question::where('id', $id)
            ->where('created_by', Auth::id())
            ->first();

        if (!$question) {
            return response()->json(['error' => 'Soal tidak ditemukan'], 404);
        }

        $question->id_topic = $request->id_topic;
        $question->save();

        return response()->json(['success' => 'Topik berhasil diperbarui']);
    }



    // 🔹 Halaman tambah soal
    public function tambahSoal()
    {
        return view('guru.tambahsoal');
    }

    // 🔹 Simpan soal baru
    public function simpanSoal(Request $request)
    {
        $request->validate([
            'type' => 'required|in:MultipleChoice,ShortAnswer',
            'question_text' => 'required|string',
            'difficulty' => 'nullable|in:easy,medium,hard',
        ]);

        $questionData = [
            'text' => $request->question_text,
            'URL' => $request->question_url ?? null,
        ];

        $mcOption = null;
        $saAnswer = null;
        $mcAnswer = null;

        if ($request->type === 'MultipleChoice') {
            $options = [];
            foreach ($request->option_text as $index => $text) {
                $label = chr(97 + $index); // a,b,c,d,e
                $options[] = [
                    $label => [
                        'teks' => $text,
                        'url' => $request->option_url[$index] ?? null
                    ]
                ];
            }
            $mcOption = json_encode($options);
            $mcAnswer = $request->mc_answer;
        } else {
            $saAnswer = json_encode($request->sa_answer);
        }

        Question::create([
            'type' => $request->type,
            'question' => json_encode($questionData),
            'MC_option' => $mcOption,
            'SA_answer' => $saAnswer,
            'MC_answer' => $mcAnswer,
            'difficulty' => $request->difficulty ?? 'medium', // default
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('tampilanSoal')->with('success', '✅ Soal berhasil disimpan!');
    }

    // 🔹 Edit soal
    public function editSoal($id)
    {
        $data = Question::findOrFail($id);
        return view('guru.editsoal', compact('data'));
    }

    // 🔹 Update soal
    public function updateSoal(Request $request, $id)
    {
        $data = Question::findOrFail($id);

        $questionData = [
            'text' => $request->question_text,
            'URL' => $request->question_url ?? null,
        ];

        $mcOption = null;
        $saAnswer = null;
        $mcAnswer = null;

        if ($data->type === 'MultipleChoice') {
            $options = [];
            foreach ($request->option_text as $index => $text) {
                $label = chr(97 + $index);
                $options[] = [
                    $label => [
                        'teks' => $text,
                        'url' => $request->option_url[$index] ?? null
                    ]
                ];
            }
            $mcOption = json_encode($options);
            $mcAnswer = $request->mc_answer;
        } else {
            $saAnswer = json_encode($request->sa_answer);
        }

        $data->update([
            'question' => json_encode($questionData),
            'MC_option' => $mcOption,
            'SA_answer' => $saAnswer,
            'MC_answer' => $mcAnswer,
            'difficulty' => $request->difficulty ?? $data->difficulty,
        ]);

        return redirect()->route('tampilanSoal')->with('success', '✅ Soal berhasil diperbarui!');
    }

    // 🔹 Hapus soal
    public function hapusSoal($id)
    {
        $data = Question::findOrFail($id);
        $data->delete();
        return redirect()->route('tampilanSoal')->with('success', '🗑️ Soal berhasil dihapus!');
    }

}
