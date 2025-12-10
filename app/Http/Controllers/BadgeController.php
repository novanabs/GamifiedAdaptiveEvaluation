<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BadgeController extends Controller
{
    // App/Http/Controllers/BadgeController.php

    public function claim(Request $request)
    {
        $user = $request->user();
        $badgeId = (int) $request->input('badge_id');
        $classId = $request->input('class_id'); // nullable, bisa dikirim dari frontend

        // validasi badge ada
        $badge = DB::table('badge')->where('id', $badgeId)->first();
        if (!$badge) {
            return response()->json(['success' => false, 'message' => 'Badge tidak ditemukan.'], 404);
        }

        // jika mengklaim per-kelas: wajib cek user anggota kelas itu
        if ($classId !== null) {
            $classId = (int) $classId;
            $isMember = DB::table('student_classes')
                ->where('id_student', $user->id)
                ->where('id_class', $classId)
                ->exists();
            if (!$isMember) {
                return response()->json(['success' => false, 'message' => 'Anda bukan anggota kelas ini.'], 403);
            }
        }

        // cek sudah diklaim untuk scope kelas ini (perhatikan id_class)
        $already = DB::table('user_badge')
            ->where('id_student', $user->id)
            ->where('id_badge', $badgeId)
            ->where(function ($q) use ($classId) {
                if ($classId === null) {
                    $q->whereNull('id_class');
                } else {
                    $q->where('id_class', $classId);
                }
            })
            ->exists();

        if ($already) {
            return response()->json(['success' => false, 'message' => 'Sudah diklaim sebelumnya untuk scope ini.', 'claimed' => true], 409);
        }

        // simpan klaim (transaction + tangani unique constraint race)
        try {
            DB::beginTransaction();
            DB::table('user_badge')->insert([
                'id_student' => $user->id,
                'id_badge' => $badgeId,
                'id_class' => $classId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // kemungkinan unique constraint (race) -> treat as already claimed
            return response()->json(['success' => false, 'message' => 'Sudah diklaim (konflik).'], 409);
        }

        $badgeData = [
            'id' => $badge->id,
            'name' => $badge->name,
            'description' => $badge->description,
            'path_icon' => $badge->path_icon ? asset($badge->path_icon) : asset('img/default.png'),
            'claimed_scope' => ['id_class' => $classId]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Badge berhasil diklaim untuk kelas.',
            'badge' => $badgeData,
        ]);
    }



    public function eligibility(Request $request, $id)
    {
        $user = $request->user();
        $badgeId = (int) $id;

        // ambil kelas-kelas yang sudah diklaim user untuk badge ini (nullable)
        $claimedRows = DB::table('user_badge')
            ->where('id_student', $user->id)
            ->where('id_badge', $badgeId)
            ->pluck('id_class')
            ->toArray(); // may contain null if global claim exists

        // Jika user sudah klaim global (NULL in id_class) => tidak perlu lanjut
        if (in_array(null, $claimedRows, true)) {
            return response()->json([
                'eligible' => false,
                'claimed' => true,
                'reason' => 'Sudah diklaim (global).',
                'claimed_classes' => $claimedRows,
                'matches' => []
            ]);
        }

        // panggil helper sesuai badge id
        if ($badgeId === 1) {
            $res = $this->checkBadgeEligibilityForFastest($user->id);
        } elseif ($badgeId === 2) {
            $res = $this->checkBadgeEligibilityForTop3($user->id);
        } elseif ($badgeId === 3) {
            $res = $this->checkBadgeEligibilityForFullCorrectPerActivity($user->id);
        } else {
            $res = ['eligible' => false, 'reason' => 'Aturan klaim untuk badge ini belum diatur.', 'matches' => []];
        }

        // tandai tiap match apakah sudah diklaim untuk kelas tersebut
        $claimedClassIds = array_filter($claimedRows, function ($v) {
            return $v !== null; });
        $claimedClassIds = array_map('intval', $claimedClassIds);

        if (isset($res['matches']) && is_array($res['matches'])) {
            foreach ($res['matches'] as &$m) {
                $m['already_claimed'] = in_array((int) ($m['class_id'] ?? 0), $claimedClassIds, true);
            }
            unset($m);
        }

        return response()->json(array_merge($res, [
            'claimed' => false,
            'claimed_classes' => $claimedClassIds
        ]));
    }

    /**
     * Helper: Fastest (per-class)
     * Cari aktivitas user dengan waktu pengerjaan valid dan tentukan kelas aktivitas tsb.
     * Jika waktu < 40% durasi maka class -> match.
     */
    protected function checkBadgeEligibilityForFastest(int $userId): array
    {
        try {
            // ambil activity_result + activity + topic + subject -> supaya dapat id_class
            $rows = DB::table('activity_result as ar')
                ->join('activities as a', 'ar.id_activity', '=', 'a.id')
                ->join('topics as t', 'a.id_topic', '=', 't.id')
                ->join('subject as s', 't.id_subject', '=', 's.id')
                ->where('ar.id_user', $userId)
                ->whereNotNull('ar.waktu_mengerjakan')
                ->whereNotNull('a.durasi_pengerjaan')
                ->select(
                    'ar.id_activity as activity_id',
                    'ar.waktu_mengerjakan',
                    'a.durasi_pengerjaan',
                    'a.title as activity_title',
                    's.id as class_id'
                )
                ->get();
        } catch (\Exception $e) {
            \Log::error('checkBadgeEligibilityForFastest - DB error', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ['eligible' => false, 'reason' => 'Terjadi kesalahan server saat memeriksa syarat.', 'matches' => []];
        }

        if ($rows->isEmpty()) {
            return ['eligible' => false, 'reason' => 'Tidak ada data pengerjaan aktivitas.', 'matches' => []];
        }

        $matchesPerClass = []; // keyed by class_id
        $checked = 0;
        $skipped = 0;

        foreach ($rows as $r) {
            $durasiRaw = $r->durasi_pengerjaan;
            if (!is_numeric($durasiRaw)) {
                $skipped++;
                continue;
            }
            $durasiMenit = (float) $durasiRaw;
            if ($durasiMenit <= 0) {
                $skipped++;
                continue;
            }

            $durasiDetik = $durasiMenit * 60.0;
            $batas40 = $durasiDetik * 0.4;

            $waktuRaw = $r->waktu_mengerjakan;
            $waktuDetik = null;
            if (is_numeric($waktuRaw)) {
                $waktuDetik = (float) $waktuRaw;
            } elseif (is_string($waktuRaw) && preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $waktuRaw)) {
                [$h, $m, $s] = explode(':', $waktuRaw);
                $waktuDetik = ((int) $h) * 3600 + ((int) $m) * 60 + ((int) $s);
            } elseif (is_string($waktuRaw) && preg_match('/^\d+(\.\d+)?$/', $waktuRaw)) {
                $waktuDetik = (float) $waktuRaw;
            } else {
                \Log::warning('checkBadgeEligibilityForFastest - unknown waktu format', ['user_id' => $userId, 'activity' => $r->activity_id, 'raw' => $waktuRaw]);
                $skipped++;
                continue;
            }

            $checked++;

            // jika waktu lebih cepat dari batas 40% -> catat match untuk kelas ini
            if ($waktuDetik < $batas40) {
                $cid = (int) $r->class_id;
                // simpan best (paling jauh di bawah batas) per class
                $diff = $batas40 - $waktuDetik;
                if (!isset($matchesPerClass[$cid]) || $diff > $matchesPerClass[$cid]['diff_detik']) {
                    $matchesPerClass[$cid] = [
                        'class_id' => $cid,
                        'class_name' => DB::table('classes')->where('id', $cid)->value('name') ?? ("ID {$cid}"),
                        'note' => 'Fastest (under 40% of duration)',
                        'activity_id' => $r->activity_id,
                        'activity_title' => $r->activity_title,
                        'waktu_detik' => $waktuDetik,
                        'batas_40_detik' => $batas40,
                        'diff_detik' => $diff
                    ];
                }
            }
        }

        if (empty($matchesPerClass)) {
            $best = null;
            // optionally keep best overall for message
            foreach ($rows as $r) {
                // parse waktu minimal sama seperti di atas (sederhana)
                if (!is_numeric($r->durasi_pengerjaan))
                    continue;
                $durasiDetik = ((float) $r->durasi_pengerjaan) * 60;
                $batas40 = $durasiDetik * 0.4;
                $w = is_numeric($r->waktu_mengerjakan) ? (float) $r->waktu_mengerjakan : null;
                if ($w === null)
                    continue;
                $diff = $batas40 - $w;
                if ($best === null || $diff > $best['diff']) {
                    $best = ['activity_id' => $r->activity_id, 'waktu_detik' => $w, 'batas' => $batas40, 'diff' => $diff];
                }
            }

            $msg = $best
                ? "Belum eligible. Waktu terbaik Anda: {$best['waktu_detik']} detik, harus < {$best['batas']} detik (40%)."
                : "Belum eligible. Tidak ditemukan waktu yg valid.";
            return ['eligible' => false, 'reason' => $msg, 'matches' => []];
        }

        // debug jika diperlukan
        $debug = null;
        if (config('app.debug')) {
            $debug = ['checked_rows' => $checked, 'skipped_rows' => $skipped, 'matches_count' => count($matchesPerClass)];
        }

        $matches = array_values($matchesPerClass);
        $resp = ['eligible' => true, 'reason' => 'Ditemukan kelas tempat Anda cepat (<40% durasi).', 'matches' => $matches];
        if ($debug)
            $resp['debug'] = $debug;
        return $resp;
    }

    /**
     * Helper: Top3 (per-class)
     * Kumpulkan semua kelas di mana user berada di top 3 (berdasarkan SUM(result) pada aktivitas kelas itu).
     */
    protected function checkBadgeEligibilityForTop3(int $userId): array
    {
        $classIds = DB::table('student_classes')
            ->where('id_student', $userId)
            ->pluck('id_class');

        if ($classIds->isEmpty()) {
            return ['eligible' => false, 'reason' => 'Anda belum tergabung di kelas manapun.', 'matches' => []];
        }

        $matches = [];

        foreach ($classIds as $classId) {
            // ambil subject -> topic -> activity ids untuk kelas ini
            $subjectIds = DB::table('subject')->where('id_class', $classId)->pluck('id');
            if ($subjectIds->isEmpty())
                continue;

            $topicIds = DB::table('topics')->whereIn('id_subject', $subjectIds)->pluck('id');
            if ($topicIds->isEmpty())
                continue;

            $activityIds = DB::table('activities')->whereIn('id_topic', $topicIds)->pluck('id');
            if ($activityIds->isEmpty())
                continue;

            // top 3 users by total score in these activities
            $topUsers = DB::table('activity_result')
                ->whereIn('id_activity', $activityIds)
                ->select('id_user', DB::raw('SUM(result) as total_score'))
                ->groupBy('id_user')
                ->orderByDesc('total_score')
                ->limit(3)
                ->pluck('id_user')
                ->toArray();

            if (in_array($userId, $topUsers, true)) {
                $className = DB::table('classes')->where('id', $classId)->value('name') ?? "ID {$classId}";
                $matches[] = [
                    'class_id' => (int) $classId,
                    'class_name' => $className,
                    'note' => 'Top 3 by total score',
                ];
            }
        }

        if (empty($matches)) {
            return ['eligible' => false, 'reason' => 'Anda belum masuk Top 3 di kelas manapun.', 'matches' => []];
        }

        return ['eligible' => true, 'reason' => 'Ditemukan kelas tempat Anda berada di Top 3.', 'matches' => $matches];
    }

    /**
     * Helper: FullCorrectPerActivity (per-class)
     * Kumpulkan semua kelas di mana ada activity_result user dengan status_benar = 1
     */
    protected function checkBadgeEligibilityForFullCorrectPerActivity(int $userId): array
    {
        try {
            $classIds = DB::table('student_classes')
                ->where('id_student', $userId)
                ->pluck('id_class')
                ->unique()
                ->values()
                ->toArray();

            if (empty($classIds)) {
                return ['eligible' => false, 'reason' => 'Anda belum tergabung di kelas manapun.', 'matches' => []];
            }

            $matches = [];

            foreach ($classIds as $classId) {
                $subjectIds = DB::table('subject')->where('id_class', $classId)->pluck('id');
                if ($subjectIds->isEmpty())
                    continue;

                $topicIds = DB::table('topics')->whereIn('id_subject', $subjectIds)->pluck('id');
                if ($topicIds->isEmpty())
                    continue;

                $activityIds = DB::table('activities')->whereIn('id_topic', $topicIds)->pluck('id');
                if ($activityIds->isEmpty())
                    continue;

                $results = DB::table('activity_result')
                    ->where('id_user', $userId)
                    ->whereIn('id_activity', $activityIds)
                    ->where('status_benar', 1)
                    ->select('id', 'id_activity', 'total_benar', 'status_benar', 'updated_at')
                    ->orderBy('updated_at', 'desc')
                    ->get();

                if ($results->isEmpty())
                    continue;

                // ambil activity titles
                $foundActivityIds = $results->pluck('id_activity')->unique()->values()->toArray();
                $activityTitles = DB::table('activities')->whereIn('id', $foundActivityIds)->pluck('title', 'id')->toArray();

                // buat match per class (kita bisa memilih paling baru atau daftar)
                $first = $results->first();
                $aid = $first->id_activity;
                $matches[] = [
                    'class_id' => (int) $classId,
                    'class_name' => DB::table('classes')->where('id', $classId)->value('name') ?? "ID {$classId}",
                    'note' => 'Full correct on activity',
                    'activity_id' => $aid,
                    'activity_title' => $activityTitles[$aid] ?? null,
                    'result_id' => $first->id,
                    'total_benar' => $first->total_benar,
                    'updated_at' => $first->updated_at,
                ];
            }

            if (empty($matches)) {
                return ['eligible' => false, 'reason' => 'Belum ada aktivitas tuntas sempurna di kelas Anda.', 'matches' => []];
            }

            return ['eligible' => true, 'reason' => 'Ditemukan aktivitas tuntas sempurna pada beberapa kelas.', 'matches' => $matches];

        } catch (\Exception $e) {
            \Log::error('checkBadgeEligibilityForFullCorrectPerActivity error', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ['eligible' => false, 'reason' => 'Terjadi kesalahan saat memeriksa syarat klaim.', 'matches' => []];
        }
    }

}
