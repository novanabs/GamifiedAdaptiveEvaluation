<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityResult;
use App\Models\nilai;
use App\Models\Question;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class aktivitasController extends Controller
{
    public function aktivitasSiswa()
    {
        $user = Auth::user();

        // 🔹 Ambil data badge
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

        // 🔹 Ambil aktivitas + nilai
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

        // 🔹 Kelompokkan per-topik TANPA batasi status (semua aktivitas masuk list)
        $activities = $rawActivities->groupBy('id_topic')->map(function ($group) {
            return (object) [
                'id_topic' => $group->first()->id_topic,
                'topik' => $group->first()->topik,
                'mapel' => $group->first()->mapel,
                'tanggal' => $group->first()->created_at,
                'list' => $group        // ← di sini semua aktivitas dimasukkan
            ];
        });

        // 🔹 Statistik
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

        // Ambil relasi lengkap berdasarkan id_topic
        $info = DB::table('topics')
            ->join('subject', 'topics.id_subject', '=', 'subject.id')
            ->join('classes', 'subject.id_class', '=', 'classes.id')
            ->where('topics.id', $activity->id_topic)
            ->select(
                'topics.title as topik',
                'subject.name as mapel',
                'classes.name as kelas'
            )
            ->first();

        return view('siswa.menjawabSoal', [
            'judul' => $activity->title,
            'kelas' => $info->kelas,
            'mapel' => $info->mapel,
            'topik' => $info->topik,
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

        $map = [11 => 5, 26 => 10, 41 => 15, 56 => 20, 71 => 25, 86 => 30];
        if ($adaptive) {
            $jumlahSoal = $map[$totalDB] ?? 5;
        } else {
            $jumlahSoal = $totalDB;
        }

        session([
            "activity.$id.current" => 0,
            "activity.$id.streak_correct" => 0,
            "activity.$id.streak_wrong" => 0,
            "activity.$id.difficulty" => "sedang",
            "activity.$id.totalQuestions" => $jumlahSoal,
            "activity.$id.used_questions" => [],
        ]);

        // simpan start_time ke session + DB (sudah saya jelaskan sebelumnya)
        $startTime = Carbon::now();
        session(["activity.$id.start_time" => $startTime->toDateTimeString()]);

        $userId = auth()->id();
        ActivityResult::updateOrCreate(
            ['id_activity' => $id, 'id_user' => $userId],
            ['start_time' => $startTime, 'waktu_mengerjakan' => null, 'end_time' => null]
        );

        // baca durasi dari activity (dalam menit)
        $durasiMenit = $activity->durasi_pengerjaan ? (int) $activity->durasi_pengerjaan : null;

        return response()->json([
            'mode' => $adaptive ? 'adaptive' : 'normal',
            'level' => session("activity.$id.difficulty"),
            'totalQuestions' => $jumlahSoal,
            'started_at' => $startTime->toDateTimeString(),
            'durasi_pengerjaan' => $durasiMenit // dikirim ke front-end
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
            // Mode normal urut biasa
            $question = $activity->questions()
                ->orderBy('id')
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

        // ========================
        // HANYA ADAPTIVE yang pakai used_questions
        // ========================
        if ($adaptive) {
            $used[] = $question->id;
            session(["activity.$id.used_questions" => $used]);
        }

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
        // Ambil start_time dari DB jika ada, kalau tidak ambil dari session
        $activityResult = ActivityResult::where('id_activity', $id)
            ->where('id_user', $userId)
            ->first();

        if ($activityResult && $activityResult->start_time) {
            $start = Carbon::parse($activityResult->start_time);
        } else {
            $startString = session("activity.$id.start_time", null);
            $start = $startString ? Carbon::parse($startString) : Carbon::now();
        }

        $end = Carbon::now();

        // hitung durasi dalam detik
        $durationSeconds = max(0, $end->getTimestamp() - $start->getTimestamp());

        // Simpan hasil ke DB (update existing record atau buat baru)
        ActivityResult::updateOrCreate(
            [
                'id_activity' => $id,
                'id_user' => $userId,
            ],
            [
                'result' => $totalBase,
                'bonus_poin' => $bonusPoint,
                'real_poin' => $totalReal,
                'result_status' => $status,
                'waktu_mengerjakan' => $durationSeconds,
                'start_time' => $start,
                'end_time' => $end,
            ]
        );

        // bersihkan session
        session()->forget("activity.$id");

        return response()->json([
            'status' => 'saved',
            'duration_seconds' => $durationSeconds,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
        ]);
    }




}
