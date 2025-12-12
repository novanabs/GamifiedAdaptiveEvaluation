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
                'classes.id as id_class',
                'classes.name as nama_kelas',
                'classes.level as level_kelas',
                'activities.created_at',
                // 🔹 pastikan kolom deadline ini ada, kalau beda nama ganti di sini
                'activities.deadline',
                DB::raw('COALESCE(activity_result.result, "-") as result'),
                DB::raw('COALESCE(activity_result.result_status, "Belum Dikerjakan") as result_status')
            )
            ->get();

        // 🔹 List paling atas: semua yang Belum Dikerjakan, urut deadline terdekat
        $belumDikerjakan = $rawActivities
            ->where('result_status', 'Belum Dikerjakan')
            ->sortBy(function ($item) {
                return $item->deadline ?? $item->created_at;
            })
            ->values();

        // 🔹 Activities per kelas
        $activitiesByClass = $rawActivities
            ->groupBy('id_class')
            ->map(function ($group) {
                // urutkan di dalam kelas:
                // 1) Belum Dikerjakan
                // 2) Remedial
                // 3) Pass
                // 4) lainnya
                $sortedList = $group->sortBy(function ($item) {
                    $status = $item->result_status;

                    if ($status === 'Belum Dikerjakan') {
                        $order = 0;
                    } elseif ($status === 'Remedial') {
                        $order = 1;
                    } elseif ($status === 'Pass') {
                        $order = 2;
                    } else {
                        $order = 3;
                    }

                    $tanggal = $item->deadline ?? $item->created_at;

                    return $order . '|' . $tanggal;
                })->values();

                return (object) [
                    'id_class' => $group->first()->id_class,
                    'nama_kelas' => $group->first()->nama_kelas,
                    'level_kelas' => $group->first()->level_kelas,
                    'list' => $sortedList,
                ];
            })
            // urutkan kelas: level lalu nama
            ->sortBy(function ($kelas) {
                return $kelas->level_kelas . '|' . $kelas->nama_kelas;
            })
            ->values();

        // 🔹 Statistik
        $jumlahAktivitas = $rawActivities->count();
        $jumlahRemedial = $rawActivities->where('result_status', 'Remedial')->count();

        // 🔹 Kirim ke view
        return view('siswa.aktivitas', [
            'user' => $user,
            'badge' => $badge,
            'kelasList' => $kelasList,
            'belumDikerjakan' => $belumDikerjakan,
            'activitiesByClass' => $activitiesByClass,
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
            'durasi' => $activity->durasi_pengerjaan,
            'jumlah_soal' => $activity->jumlah_soal,
        ]);
    }

    public function start($id)
    {
        // bersihkan session lama untuk activity ini
        session()->forget("activity.$id");

        $activity = Activity::findOrFail($id);

        // total soal yang tersimpan di DB (jumlah baris di pivot activity_question)
        $totalDB = $activity->questions()->count();

        // apakah mode adaptive?
        $adaptive = ($activity->addaptive === 'yes');

        // ambil jumlah soal dari kolom activities.jumlah_soal jika tersedia,
        // jika null maka fallback ke totalDB
        $jumlahSoal = $activity->jumlah_soal ?? $totalDB;

        // pastikan jumlahSoal tidak melebihi total soal yang tersedia
        // (opsional — tapi umumnya aman untuk membatasi)
        if ($jumlahSoal > $totalDB) {
            $jumlahSoal = $totalDB;
        }

        // inisialisasi session untuk aktivitas ini
        session([
            "activity.$id.current" => 0,
            "activity.$id.streak_correct" => 0,
            "activity.$id.streak_wrong" => 0,
            "activity.$id.difficulty" => "sedang",
            "activity.$id.totalQuestions" => $jumlahSoal,
            "activity.$id.used_questions" => [],
            "activity.$id.total_correct" => 0,
        ]);

        // simpan start_time ke session + DB
        $startTime = Carbon::now();
        session(["activity.$id.start_time" => $startTime->toDateTimeString()]);

        $userId = auth()->id();
        ActivityResult::updateOrCreate(
            ['id_activity' => $id, 'id_user' => $userId],
            ['start_time' => $startTime, 'waktu_mengerjakan' => null, 'end_time' => null, 'total_benar' => null]
        );

        // baca durasi dari activity (dalam menit), kirim ke front-end
        $durasiMenit = $activity->durasi_pengerjaan ? (int) $activity->durasi_pengerjaan : null;

        return response()->json([
            'mode' => $adaptive ? 'adaptive' : 'normal',
            'level' => session("activity.$id.difficulty"),
            'totalQuestions' => $jumlahSoal,
            'started_at' => $startTime->toDateTimeString(),
            'durasi_pengerjaan' => $durasiMenit
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
        // Hitung total jawaban benar (akumulasi)
        $prevCorrect = session("activity.$id.total_correct", 0);
        if ($correct) {
            session(["activity.$id.total_correct" => $prevCorrect + 1]);
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

        // Status kelulusan (angka)
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
        $totalCorrect = session("activity.$id.total_correct", 0);

        // ====== DAPATKAN JUMLAH SOAL YANG DIPAKAI ======
        // Prioritaskan nilai yang disimpan di session saat start() (adaptive/normal)
        $jumlahSoal = session("activity.$id.totalQuestions", null);

        if ($jumlahSoal === null) {
            // fallback: hitung dari relasi questions (pastikan ini merepresentasikan soal yang dipakai)
            $activity = Activity::find($id);
            $jumlahSoal = $activity ? $activity->questions()->count() : 0;
        } else {
            // pastikan integer
            $jumlahSoal = (int) $jumlahSoal;
            // dan ambil activity juga untuk penggunaan selanjutnya
            $activity = Activity::find($id);
        }

        // tentukan statusBenar: true jika totalCorrect sama persis dengan jumlahSoal
        $statusBenar = ($totalCorrect === $jumlahSoal) ? true : false;

        // =============== HITUNG BEST CASE (REVISI) ===============
        // ambil poin dari settings (cast ke float supaya aman)
        $pointEasy = (float) (Settings::where('name', 'soal_mudah')->value('value') ?? 0);
        $pointMedium = (float) (Settings::where('name', 'soal_sedang')->value('value') ?? 0);
        $pointHard = (float) (Settings::where('name', 'soal_sulit')->value('value') ?? 0);

        // pastikan $jumlahSoal integer (sudah di-cast di atas)
        $jumlahSoal = (int) $jumlahSoal;

        if ($activity && $activity->addaptive === 'yes') {
            // REVISI: best-case adaptive = 2 medium (maks) + sisa = hard
            $mediumBest = min(2, $jumlahSoal);                // paling banyak 2 medium
            $hardBest = max(0, $jumlahSoal - $mediumBest);  // sisanya hard
            $easyBest = 0;

            $bestCase = ($easyBest * $pointEasy) + ($mediumBest * $pointMedium) + ($hardBest * $pointHard);
        } else {
            // NON-ADAPTIVE: best-case = komposisi soal yang ada di DB (semua benar)
            if ($activity) {
                $easyCount = $activity->questions()->where('difficulty', 'mudah')->count();
                $mediumCount = $activity->questions()->where('difficulty', 'sedang')->count();
                $hardCount = $activity->questions()->where('difficulty', 'sulit')->count();
            } else {
                $easyCount = $mediumCount = $hardCount = 0;
            }

            $bestCase = ($easyCount * $pointEasy) + ($mediumCount * $pointMedium) + ($hardCount * $pointHard);
        }

        // hindari pembagian dengan 0 -> kalau bestCase 0 set 1 supaya nilai_akhir jadi 0 ketika totalReal 0
        if ($bestCase <= 0) {
            $bestCase = 1;
        }

        // nilai akhir = (real_poin / best_case) * 100
        $nilaiAkhir = round((($totalBase) / $bestCase) * 100, 2);

        // ====================== SIMPAN KE DB ======================
        ActivityResult::updateOrCreate(
            [
                'id_activity' => $id,
                'id_user' => $userId,
            ],
            [
                'result' => $totalReal,
                'bonus_poin' => $bonusPoint,
                'real_poin' => $totalBase,
                'result_status' => $status,
                'waktu_mengerjakan' => $durationSeconds,
                'total_benar' => $totalCorrect,
                'start_time' => $start,
                'end_time' => $end,
                'status_benar' => $statusBenar,
                'nilai_akhir' => $nilaiAkhir,
            ]
        );

        // Ambil record lagi dari DB untuk dikembalikan ke frontend (source of truth)
        $activityResult = ActivityResult::where('id_activity', $id)
            ->where('id_user', $userId)
            ->first();

        // bersihkan session
        session()->forget("activity.$id");
        session()->forget("activity.$id.total_correct");

        return response()->json([
            'status' => 'saved',
            // ringkasan cepat
            'duration_seconds' => $durationSeconds,
            'total_correct' => $totalCorrect,
            'jumlah_soal' => $jumlahSoal,
            // data dari DB (string/angka/timestamp)
            'result_db' => $activityResult ? [
                'result' => $activityResult->result,
                'bonus_poin' => $activityResult->bonus_poin,
                'real_poin' => $activityResult->real_poin,
                'result_status' => $activityResult->result_status,
                'waktu_mengerjakan' => $activityResult->waktu_mengerjakan,
                'total_benar' => $activityResult->total_benar,
                'start_time' => optional($activityResult->start_time)->toDateTimeString(),
                'end_time' => optional($activityResult->end_time)->toDateTimeString(),
                'status_benar' => (bool) $activityResult->status_benar,
                'nilai_akhir' => $activityResult->nilai_akhir,
            ] : null,
        ]);
    }






}
