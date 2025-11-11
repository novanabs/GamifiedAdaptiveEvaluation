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

        // Ambil semua kelas milik guru yang login
        $kelas = DB::table('classes')
            ->where('created_by', Auth::id())
            ->get();

        // Default nilai awal
        $siswa = collect();
        $kelasTerpilih = null;

        if ($token) {
            $kelasTerpilih = DB::table('classes')->where('token', $token)->first();

            if ($kelasTerpilih) {
                $siswa = DB::table('student_classes')
                    ->join('users', 'student_classes.id_student', '=', 'users.id')
                    ->select('users.name', 'users.email')
                    ->where('student_classes.id_class', $kelasTerpilih->id)
                    ->get();
            }
        }

        return view('guru.datasiswa', compact('kelas', 'siswa', 'kelasTerpilih', 'token'));
    }
    //export data siswa
    public function exportSiswa(Request $request)
    {
        $token = $request->query('token');

        $kelas = DB::table('classes')->where('token', $token)->first();
        if (!$kelas) {
            return redirect()->route('dataSiswa')->with('error', 'Kelas tidak ditemukan.');
        }

        $siswa = DB::table('student_classes')
            ->join('users', 'student_classes.id_student', '=', 'users.id')
            ->select('users.name', 'users.email')
            ->where('student_classes.id_class', $kelas->id)
            ->get();

        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'Nama Siswa');
        $sheet->setCellValue('B1', 'Email');
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        // Isi data
        $row = 2;
        foreach ($siswa as $data) {
            $sheet->setCellValue('A' . $row, $data->name);
            $sheet->setCellValue('B' . $row, $data->email);
            $row++;
        }

        // Nama file
        $fileName = 'Data_Siswa_' . str_replace(' ', '_', $kelas->name) . '.xlsx';

        // Stream file ke browser
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

        // Ambil kelas yang diajarkan oleh guru ini
        $kelas = DB::table('classes')
            ->join('teacher_classes', 'classes.id', '=', 'teacher_classes.id_class')
            ->where('teacher_classes.id_teacher', $idGuru)
            ->select('classes.*')
            ->distinct()
            ->get();

        // Ambil guru, subject, topic, dan activity terkait setiap kelas
        $dataKelas = $kelas->map(function ($k) {
            // 🔹 Ambil semua guru pengajar kelas ini (bukan hanya first)
            $guru = DB::table('users')
                ->join('teacher_classes', 'users.id', '=', 'teacher_classes.id_teacher')
                ->where('teacher_classes.id_class', $k->id)
                ->pluck('users.name'); // ->pluck menghasilkan koleksi nama

            // 🔹 Ambil data lain
            $subjects = DB::table('subject')->where('id_class', $k->id)->pluck('name');
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
                'guru' => $guru, // <-- simpan collection guru
                'subjects' => $subjects,
                'topics' => $topics,
                'activities' => $activities,
            ];
        });

        return view('guru.datakelas', compact('dataKelas'));
    }

    public function tambahKelas(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $token = strtoupper(Str::random(8));

        $kelas = Classes::create([
            'name' => $request->name,
            'description' => $request->description,
            'level' => $request->level,
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

        return redirect()->route('guru.dataSubject')->with('success', 'Subject berhasil ditambahkan!');
    }

    // Edit subject
    public function updateSubject(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $subject = Subject::findOrFail($id);
        $subject->update(['name' => $request->name]);
        return redirect()->route('guru.dataSubject')->with('success', 'Subject berhasil diperbarui!');
    }

    // Hapus subject
    public function hapusSubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();
        return redirect()->route('guru.dataSubject')->with('success', 'Subject berhasil dihapus!');
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
        $data = Topic::with(['activities', 'subject.classes'])
            ->where('created_by', Auth::id())
            ->get();

        // ambil soal yang dibuat oleh guru yang sama
        $questions = Question::where('created_by', Auth::id())->get();
        // Ambil mapping soal-aktivitas
        $activityQuestions = DB::table('activity_question')->get();

        return view('guru.dataaktivitas', compact('data', 'questions', 'activityQuestions'));
    }


    /**
     * Menyimpan aktivitas baru.
     */
    public function simpanAktivitas(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:basic,additional,remedial',
            'type' => 'required|in:task,quiz',
            'deadline' => 'nullable|date',
            'id_topic' => 'required|exists:topics,id'
        ]);

        Activity::create([
            'title' => $request->title,
            'status' => $request->status,
            'type' => $request->type,
            'deadline' => $request->deadline,
            'id_topic' => $request->id_topic,
        ]);

        return redirect()->route('guru.aktivitas.tampil')->with('success', 'Aktivitas berhasil ditambahkan.');
    }

    /**
     * Mengubah data aktivitas.
     */
    public function ubahAktivitas(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:basic,additional,remedial',
            'type' => 'required|in:task,quiz',
            'deadline' => 'nullable|date'
        ]);

        $aktivitas = Activity::findOrFail($id);
        $aktivitas->update([
            'title' => $request->title,
            'status' => $request->status,
            'type' => $request->type,
            'deadline' => $request->deadline,
        ]);

        return redirect()->route('guru.aktivitas.tampil')->with('success', 'Aktivitas berhasil diperbarui.');
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
    public function tampilanSoal()
    {
        $data = Question::where('created_by', Auth::id())->get();

        // Decode JSON agar siap dikirim ke Blade
        foreach ($data as $item) {
            $item->question = json_decode($item->question);
            $item->MC_option = $item->MC_option ? json_decode($item->MC_option) : null;
            $item->SA_answer = $item->SA_answer ? json_decode($item->SA_answer) : null;
        }

        return view('guru.datasoal', compact('data'));
    }


    // Halaman tambah soal
    public function tambahSoal()
    {
        return view('guru.tambahsoal');
    }

    // Simpan soal baru
    public function simpanSoal(Request $request)
    {
        $request->validate([
            'type' => 'required|in:MultipleChoice,ShortAnswer',
            'question_text' => 'required|string',
        ]);

        // Simpan data JSON untuk pertanyaan
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
                $label = chr(97 + $index); // menghasilkan a,b,c,d,...
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
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('tampilanSoal')->with('success', 'Soal berhasil disimpan!');
    }

    // Edit soal
    public function editSoal($id)
    {
        $data = Question::findOrFail($id);
        return view('guru.editsoal', compact('data'));
    }

    // Update soal
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

        if ($request->type === 'MultipleChoice') {
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
        ]);

        return redirect()->route('tampilanSoal')->with('success', 'Soal berhasil diperbarui!');
    }

    // Hapus soal
    public function hapusSoal($id)
    {
        $data = Question::findOrFail($id);
        $data->delete();
        return redirect()->route('tampilanSoal')->with('success', 'Soal berhasil dihapus!');
    }

    public function simpanAturSoal(Request $request, $id_activity)
    {
        $selected = $request->input('id_question', []);

        $existing = DB::table('activity_question')
            ->where('id_activity', $id_activity)
            ->pluck('id_question')
            ->toArray();

        $toAdd = array_diff($selected, $existing);
        $toRemove = array_diff($existing, $selected);

        foreach ($toAdd as $q) {
            DB::table('activity_question')->insert([
                'id_activity' => $id_activity,
                'id_question' => $q,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!empty($toRemove)) {
            DB::table('activity_question')
                ->where('id_activity', $id_activity)
                ->whereIn('id_question', $toRemove)
                ->delete();
        }

        return redirect()->back()->with('success', 'Soal aktivitas berhasil diperbarui!');
    }
}
