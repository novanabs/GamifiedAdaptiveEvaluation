<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityResult;
use App\Models\nilai;
use App\Models\Question;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class aktivitasController extends Controller
{
    public function aktivitasSiswa()
    {
        $user = Auth::user();

        // 🔹 Ambil data badge siswa
        $badge = DB::table('user_badge')
            ->join('badge', 'user_badge.id_badge', '=', 'badge.id')
            ->where('user_badge.id_student', $user->id)
            ->select('badge.name', 'badge.description')
            ->first();

        // 🔹 Ambil daftar kelas siswa
        $kelasList = DB::table('student_classes')
            ->join('classes', 'student_classes.id_class', '=', 'classes.id')
            ->where('student_classes.id_student', $user->id)
            ->select('classes.id', 'classes.name', 'classes.level', 'classes.token')
            ->get();

        // 🔹 Ambil aktivitas + join nilai
        $rawActivities = DB::table('activities')
            ->join('topics', 'activities.id_topic', '=', 'topics.id')
            ->join('subject', 'topics.id_subject', '=', 'subject.id')
            ->join('classes', 'subject.id_class', '=', 'classes.id')
            ->join('student_classes', 'classes.id', '=', 'student_classes.id_class')
            ->join('users', 'student_classes.id_student', '=', 'users.id')
            ->leftJoin('activity_result', function ($join) use ($user) {
                $join->on('activities.id', '=', 'activity_result.id_activity')
                    ->where('activity_result.id_user', '=', $user->id);
            })
            ->where('users.id', $user->id)
            ->whereIn('classes.token', $kelasList->pluck('token'))
            ->select(
                'activities.id as id_activity',
                'activities.id_topic',
                'activities.title as aktivitas',
                'activities.status',
                'topics.title as topik',
                'subject.name as mapel',
                'activities.created_at',
                DB::raw('COALESCE(activity_result.result, "-") as result'),
                DB::raw('COALESCE(activity_result.result_status, "Belum Dikerjakan") as result_status')
            )
            ->orderBy('topics.id')
            ->orderBy('activities.created_at', 'asc')
            ->get();

        // 🔹 Kelompokkan aktivitas berdasarkan topik
        $activities = $rawActivities->groupBy('id_topic')->map(function ($group) {
            $data = [
                'id_topic' => $group->first()->id_topic,
                'topik' => $group->first()->topik,
                'mapel' => $group->first()->mapel,
                'tanggal' => $group->first()->created_at,
                'basic' => null,
                'additional' => null,
                'remedial' => null,
            ];


            foreach ($group as $act) {
                $status = strtolower($act->status);
                if ($status === 'basic')
                    $data['basic'] = $act;
                if ($status === 'additional')
                    $data['additional'] = $act;
                if ($status === 'remedial')
                    $data['remedial'] = $act;
            }

            return (object) $data;
        });

        // 🔹 Hitung statistik
        $jumlahAktivitas = $rawActivities->count();
        $jumlahRemedial = $rawActivities->where('result_status', 'Remedial')->count();

        // 🔹 Kirim ke view
        return view('siswa.aktivitas', [
            'user' => $user,
            'badge' => $badge,
            'kelasList' => $kelasList,
            'activities' => $activities,
            'jumlahAktivitas' => $jumlahAktivitas,
            'jumlahRemedial' => $jumlahRemedial
        ]);
    }

    public function show($id)
    {
        $activity = Activity::findOrFail($id);

        return view('siswa.menjawabSoal', [
            'judul' => $activity->title,
            'topik' => $activity->status,
            'id_activity' => $activity->id,
            'addaptive' => $activity->addaptive,
        ]);
    }
    public function start($id)
    {
        session()->forget("activity.$id");

        $activity = Activity::findOrFail($id);

        $totalDB = $activity->questions()->count();
        $adaptive = $activity->addaptive === 'yes';

        // Mapping jumlah soal adaptive
        $map = [
            12 => 5,
            27 => 10,
            42 => 15,
            57 => 20,
            72 => 25,
            87 => 30
        ];

        if ($adaptive) {
            $jumlahSoal = $map[$totalDB] ?? 5; // fallback
        } else {
            $jumlahSoal = $totalDB;
        }

        session([
            "activity.$id.current" => 0,
            "activity.$id.streak_correct" => 0,
            "activity.$id.streak_wrong" => 0,
            "activity.$id.difficulty" => "sedang",
            "activity.$id.totalQuestions" => $jumlahSoal,
            "activity.$id.used_questions" => [],  // NEW
        ]);



        return response()->json([
            'mode' => $adaptive ? 'adaptive' : 'normal',
            'level' => session("activity.$id.difficulty"),
            'totalQuestions' => $jumlahSoal
        ]);
    }

    public function getQuestion(Request $req, $id)
    {
        $activity = Activity::findOrFail($id);
        $adaptive = $activity->addaptive === 'yes';
        $index = $req->query('index');

        // Ambil daftar soal yang sudah digunakan
        $used = session("activity.$id.used_questions", []);

        if ($adaptive) {

            $difficulty = session("activity.$id.difficulty", "sedang");

            // Ambil soal sesuai difficulty yang belum pernah dipakai
            $question = $activity->questions()
                ->where('difficulty', $difficulty)
                ->whereNotIn('id', $used)
                ->inRandomOrder()
                ->first();

            // Jika soal untuk difficulty ini habis → fallback difficulty lain
            if (!$question) {
                $question = $activity->questions()
                    ->whereNotIn('id', $used)
                    ->inRandomOrder()
                    ->first();
            }

        } else {
            // Mode normal tetap pakai indeks
            $question = $activity->questions()
                ->whereNotIn('id', $used)
                ->skip($index)
                ->first();
        }

        // Jika benar-benar habis (seharusnya jarang terjadi)
        if (!$question) {
            return response()->json([
                'end' => true,
                'message' => 'Tidak ada soal tersisa.'
            ]);
        }

        // Simpan ID soal ini agar tidak muncul lagi
        $used[] = $question->id;
        session(["activity.$id.used_questions" => $used]);

        return response()->json([
            'question_id' => $question->id,
            'type' => $question->type,
            'difficulty' => $question->difficulty,
            'question' => json_decode($question->question),
            'options' => json_decode($question->MC_option),
        ]);
    }

    public function submitAnswer(Request $req, $id)
    {
        $question = Question::findOrFail($req->question_id);
        $adaptive = Activity::find($id)->addaptive === 'yes';

        // =======================
        // CEK KEBENARAN JAWABAN
        // =======================
        $correct = false;

        if ($question->type === 'MultipleChoice') {
            $correct = strtolower($req->user_answer) === strtolower($question->MC_answer);

        } else if ($question->type === 'ShortAnswer') {
            $answers = json_decode($question->SA_answer, true);
            $user = strtolower(trim($req->user_answer));
            $correct = in_array($user, array_map('strtolower', $answers));
        }

        // =======================
        // LOGIKA ADAPTIVE (LEVEL)
        // =======================
        if ($adaptive) {

            $correctStreak = session("activity.$id.streak_correct", 0);
            $wrongStreak = session("activity.$id.streak_wrong", 0);
            $level = session("activity.$id.difficulty", "sedang");

            if ($correct) {
                $correctStreak++;
                $wrongStreak = 0;
            } else {
                $wrongStreak++;
                $correctStreak = 0;
            }

            if ($level === 'sedang') {
                if ($correctStreak >= 2) {
                    $level = 'sulit';
                }
                if ($wrongStreak >= 2) {
                    $level = 'mudah';
                }
            } else if ($level === 'mudah') {
                if ($correctStreak >= 2) {
                    $level = 'sedang';
                }
            } else if ($level === 'sulit') {
                if ($wrongStreak >= 2) {
                    $level = 'sedang';
                }
            }

            session([
                "activity.$id.difficulty" => $level,
                "activity.$id.streak_correct" => $correctStreak,
                "activity.$id.streak_wrong" => $wrongStreak,
            ]);
        }

        // =======================
        // HITUNG POIN SOAL INI
        // =======================

        $pointEasy = Settings::where('name', 'soal_mudah')->value('value');
        $pointMedium = Settings::where('name', 'soal_sedang')->value('value');
        $pointHard = Settings::where('name', 'soal_sulit')->value('value');

        $difficulty = $question->difficulty;

        // Tentukan base point
        $basePoint =
            $difficulty === 'mudah' ? $pointEasy :
            ($difficulty === 'sedang' ? $pointMedium : $pointHard);

        // Jika salah → basePoint = 0
        if (!$correct) {
            $basePoint = 0;
        }

        // simpan akumulasi base point
        $prevBase = session("activity.$id.total_base_point", 0);
        session(["activity.$id.total_base_point" => $prevBase + $basePoint]);

        // =======================
        // BONUS STREAK
        // =======================
        $correctStreak = session("activity.$id.streak_correct", 0);

        $bonus = 0;
        if ($correct) {
            if ($correctStreak == 2)
                $bonus = 5;
            else if ($correctStreak == 3)
                $bonus = 10;
            else if ($correctStreak >= 4)
                $bonus = 15;
        }

        // Jika salah → bonus = 0
        if (!$correct) {
            $bonus = 0;
        }

        // simpan akumulasi total real point (dasar + bonus)
        $prevReal = session("activity.$id.total_real_point", 0);
        session(["activity.$id.total_real_point" => $prevReal + ($basePoint + $bonus)]);


        return response()->json([
            'correct' => $correct,
            'new_level' => session("activity.$id.difficulty"),
            'streak_correct' => session("activity.$id.streak_correct")
        ]);

    }



    public function finishTest(Request $req, $id)
    {
        $userId = auth()->id();

        $totalBase = session("activity.$id.total_base_point", 0);  // nilai dasar
        $totalReal = session("activity.$id.total_real_point", 0);  // nilai total (dasar+bonus)

        // Bonus = totalReal - totalBase
        $bonusPoint = $totalReal - $totalBase;

        // Status kelulusan
        $status = $totalReal >= 70 ? 'Pass' : 'Remedial';

        ActivityResult::updateOrCreate(
            [
                'id_activity' => $id,
                'id_user' => $userId,
            ],
            [
                'result' => $totalBase,   // nilai dasar
                'bonus_poin' => $bonusPoint,  // total bonus
                'real_poin' => $totalReal,   // nilai akhir
                'result_status' => $status,
            ]
        );

        session()->forget("activity.$id");

        return response()->json(['status' => 'saved']);
    }


    public function saveResult(Request $request)
    {
        $request->validate([
            'id_activity' => 'required',
            'id_user' => 'required',
            'result' => 'required|numeric',
            'result_status' => 'required|string',
        ]);

        ActivityResult::create([
            'id_activity' => $request->id_activity,
            'id_user' => $request->id_user,
            'result' => $request->result,
            'result_status' => $request->result_status,
            'poin' => null,
        ]);

        return redirect()->route('siswa.aktivitas')->with('success', 'Nilai berhasil disimpan!');
    }

}
