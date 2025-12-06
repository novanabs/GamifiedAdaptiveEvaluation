<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BadgeController extends Controller
{
    public function claim(Request $request)
    {
        $user = $request->user();
        $badgeId = (int) $request->input('badge_id');

        $badge = DB::table('badge')->where('id', $badgeId)->first();
        if (!$badge) {
            if ($request->ajax())
                return response()->json(['success' => false, 'message' => 'Badge tidak ditemukan.'], 404);
            return back()->with('error', 'Badge tidak ditemukan.');
        }

        // cek sudah klaim
        $exists = DB::table('user_badge')
            ->where('id_student', $user->id)
            ->where('id_badge', $badgeId)
            ->exists();

        if ($exists) {
            if ($request->ajax())
                return response()->json(['success' => false, 'message' => 'Badge sudah diklaim.', 'claimed' => true]);
            return back()->with('info', 'Badge sudah diklaim.');
        }

        // Periksa eligibility sesuai badge id
        if ($badgeId === 1) {
            $eligibleResult = $this->checkBadgeEligibilityForFastest($user->id);
        } elseif ($badgeId === 2) {
            $eligibleResult = $this->checkBadgeEligibilityForTop3($user->id);
        } elseif ($badgeId === 3) {
            $eligibleResult = $this->checkBadgeEligibilityForFullCorrectPerActivity($user->id);
        } else {
            if ($request->ajax())
                return response()->json(['success' => false, 'message' => 'Aturan klaim untuk badge ini belum diatur.'], 400);
            return back()->with('error', 'Aturan klaim untuk badge ini belum diatur.');
        }

        if ($eligibleResult['eligible'] === false) {
            $msg = $eligibleResult['reason'] ?? 'Anda belum memenuhi syarat untuk klaim badge ini.';
            if ($request->ajax())
                return response()->json(['success' => false, 'message' => $msg], 403);
            return back()->with('error', $msg);
        }
        // insert klaim
        DB::table('user_badge')->insert([
            'id_student' => $user->id,
            'id_badge' => $badgeId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Badge berhasil diklaim!']);
        }

        return back()->with('success', 'Badge berhasil diklaim!');
    }

    public function eligibility(Request $request, $id)
    {
        $user = $request->user();
        $badgeId = (int) $id;

        // cek sudah klaim
        $exists = DB::table('user_badge')
            ->where('id_student', $user->id)
            ->where('id_badge', $badgeId)
            ->exists();

        if ($exists) {
            return response()->json([
                'eligible' => false,
                'claimed' => true,
                'reason' => 'Sudah diklaim'
            ]);
        }

        if ($badgeId === 1) {
            $res = $this->checkBadgeEligibilityForFastest($user->id);
        } elseif ($badgeId === 2) {
            $res = $this->checkBadgeEligibilityForTop3($user->id);
        } elseif ($badgeId === 3) {
            $res = $this->checkBadgeEligibilityForFullCorrectPerActivity($user->id);
        } else {
            $res = ['eligible' => false, 'reason' => 'Aturan klaim untuk badge ini belum diatur.'];
        }

        return response()->json(array_merge($res, ['claimed' => false]));
    }
    //helpers
    protected function checkBadgeEligibilityForFastest(int $userId): array
    {
        // Catatan: di DB tampak kolom nama 'waktu_mengerjakan' pada activity_result
        try {
            $rows = DB::table('activity_result')
                ->join('activities', 'activity_result.id_activity', '=', 'activities.id')
                ->where('activity_result.id_user', $userId)
                ->whereNotNull('activity_result.waktu_mengerjakan')
                ->whereNotNull('activities.durasi_pengerjaan')
                ->select(
                    'activity_result.id_activity as activity_id',
                    'activity_result.waktu_mengerjakan',
                    'activities.durasi_pengerjaan',
                    'activities.title as activity_title'
                )
                ->get();
        } catch (\Exception $e) {
            \Log::error('checkBadgeEligibilityForFastest - DB error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'eligible' => false,
                'reason' => 'Terjadi kesalahan server saat memeriksa syarat. Cek log.'
            ];
        }

        if ($rows->isEmpty()) {
            return [
                'eligible' => false,
                'reason' => 'Tidak ada data pengerjaan aktivitas.'
            ];
        }

        $best = null;
        $bestDiff = null;
        $checked = 0;
        $skipped = 0;

        foreach ($rows as $r) {
            // ambil durasi (dipastikan di DB: durasi_pengerjaan, terlihat int)
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
            $batas40 = $durasiDetik * 0.4; // 40% dari durasi (dalam detik)

            // parse waktu_mengerjakan (DB kolom terlihat int, kemungkinan detik)
            $waktuRaw = $r->waktu_mengerjakan;
            $waktuDetik = null;

            if (is_numeric($waktuRaw)) {
                $waktuDetik = (float) $waktuRaw;
            } elseif (is_string($waktuRaw) && preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $waktuRaw)) {
                // format HH:MM:SS
                [$h, $m, $s] = explode(':', $waktuRaw);
                $waktuDetik = ((int) $h) * 3600 + ((int) $m) * 60 + ((int) $s);
            } elseif (is_string($waktuRaw) && preg_match('/^\d+(\.\d+)?$/', $waktuRaw)) {
                // numeric dalam string
                $waktuDetik = (float) $waktuRaw;
            } else {
                // unknown format -> skip (log untuk debugging)
                \Log::warning('checkBadgeEligibilityForFastest - unknown waktu_mengerjakan format', [
                    'user_id' => $userId,
                    'activity_id' => $r->activity_id,
                    'waktu_raw' => $waktuRaw,
                    'durasi_raw' => $durasiRaw,
                ]);
                $skipped++;
                continue;
            }

            $checked++;

            // hitung seberapa jauh di bawah batas; positif berarti lebih cepat dari batas
            $diff = $batas40 - $waktuDetik;

            if ($bestDiff === null || $diff > $bestDiff) {
                $bestDiff = $diff;
                $best = [
                    'activity_id' => $r->activity_id,
                    'activity_title' => $r->activity_title ?? null,
                    'waktu_detik' => $waktuDetik,
                    'durasi_detik' => $durasiDetik,
                    'batas_40_detik' => $batas40,
                    'diff_detik' => $diff
                ];
            }
        }

        if (!$best) {
            return [
                'eligible' => false,
                'reason' => 'Tidak ada aktivitas dengan durasi/waktu yang valid untuk dievaluasi.'
            ];
        }

        // siapkan debug kecil jika APP_DEBUG = true
        $debug = null;
        if (config('app.debug')) {
            $debug = [
                'total_rows' => $rows->count(),
                'checked_rows' => $checked,
                'skipped_rows' => $skipped
            ];
        }

        if ($best['waktu_detik'] < $best['batas_40_detik']) {
            $message = "Eligible! Pada aktivitas " .
                ($best['activity_title'] ? "\"{$best['activity_title']}\"" : "ID {$best['activity_id']}") .
                " (ID {$best['activity_id']}), waktu pengerjaan: {$best['waktu_detik']} detik, 40% dari durasi: {$best['batas_40_detik']} detik.";

            $resp = ['eligible' => true, 'reason' => $message, 'data' => $best];
            if ($debug)
                $resp['debug'] = $debug;
            return $resp;
        }

        $resp = [
            'eligible' => false,
            'reason' => "Belum eligible. Waktu terbaik Anda: {$best['waktu_detik']} detik, harus kurang dari batas 40% yaitu {$best['batas_40_detik']} detik.",
            'data' => $best
        ];
        if ($debug)
            $resp['debug'] = $debug;
        return $resp;
    }



    protected function checkBadgeEligibilityForTop3(int $userId): array
    {
        // ambil id kelas user
        $classIds = DB::table('student_classes')
            ->where('id_student', $userId)
            ->pluck('id_class');

        if ($classIds->isEmpty()) {
            return ['eligible' => false, 'reason' => 'Anda belum tergabung di kelas manapun.'];
        }

        foreach ($classIds as $classId) {
            // ambil subject untuk kelas ini
            $subjectIds = DB::table('subject')->where('id_class', $classId)->pluck('id');
            if ($subjectIds->isEmpty())
                continue;

            // ambil topic untuk subject tersebut
            $topicIds = DB::table('topics')->whereIn('id_subject', $subjectIds)->pluck('id');
            if ($topicIds->isEmpty())
                continue;

            // ambil aktivitas untuk topic tersebut
            $activityIds = DB::table('activities')->whereIn('id_topic', $topicIds)->pluck('id');
            if ($activityIds->isEmpty())
                continue;

            // ambil top 3 users berdasarkan SUM(result) untuk aktivitas kelas ini
            // NOTE: gunakan groupBy dan orderByDesc lalu limit 3
            $topUsers = DB::table('activity_result')
                ->whereIn('id_activity', $activityIds)
                ->select('id_user', DB::raw('SUM(result) as total_score'))
                ->groupBy('id_user')
                ->orderByDesc('total_score')
                ->limit(3)
                ->pluck('id_user') // ambil array id_user
                ->toArray();

            if (in_array($userId, $topUsers, true)) {
                // ambil nama kelas untuk pesan (opsional)
                $className = DB::table('classes')->where('id', $classId)->value('name') ?? "ID {$classId}";

                return [
                    'eligible' => true,
                    'reason' => "Anda berada di posisi top 3 pada kelas \"{$className}\". Selamat!"
                ];
            }
        }

        return [
            'eligible' => false,
            'reason' => 'Anda belum berada di posisi 1–3 pada salah satu kelas Anda.'
        ];
    }

    protected function checkBadgeEligibilityForFullCorrectPerActivity(int $userId): array
    {
        try {
            // 1) ambil kelas user
            $classIds = DB::table('student_classes')
                ->where('id_student', $userId)
                ->pluck('id_class')
                ->unique()
                ->values()
                ->toArray();

            if (empty($classIds)) {
                return ['eligible' => false, 'reason' => 'Anda belum tergabung di kelas manapun.'];
            }

            // loop per kelas
            foreach ($classIds as $classId) {
                // ambil subject untuk kelas ini
                $subjectIds = DB::table('subject')
                    ->where('id_class', $classId)
                    ->pluck('id')
                    ->unique()
                    ->values()
                    ->toArray();

                if (empty($subjectIds)) {
                    continue;
                }

                // ambil topic untuk subject tersebut
                $topicIds = DB::table('topics')
                    ->whereIn('id_subject', $subjectIds)
                    ->pluck('id')
                    ->unique()
                    ->values()
                    ->toArray();

                if (empty($topicIds)) {
                    continue;
                }

                // ambil aktivitas untuk topic tersebut
                $activityIds = DB::table('activities')
                    ->whereIn('id_topic', $topicIds)
                    ->pluck('id')
                    ->unique()
                    ->values()
                    ->toArray();

                if (empty($activityIds)) {
                    continue;
                }

                // ambil semua activity_result untuk user & activity yang relevan
                // dan cek kolom status_benar = 1 (true)
                $results = DB::table('activity_result')
                    ->where('id_user', $userId)
                    ->whereIn('id_activity', $activityIds)
                    ->where('status_benar', 1)
                    ->select('id', 'id_activity', 'total_benar', 'status_benar', 'updated_at')
                    ->orderBy('updated_at', 'desc')
                    ->get();

                if ($results->isEmpty()) {
                    // tidak ada record dengan status_benar = 1 di kelas ini -> lanjut kelas berikutnya
                    continue;
                }

                // ambil judul aktivitas (opsional, untuk pesan) — hanya untuk activity yang ditemukan
                $foundActivityIds = $results->pluck('id_activity')->unique()->values()->toArray();
                $activityTitles = DB::table('activities')
                    ->whereIn('id', $foundActivityIds)
                    ->pluck('title', 'id')
                    ->toArray();

                // kembalikan hasil pertama yang memenuhi (paling baru)
                $r = $results->first();
                $aid = $r->id_activity;
                $title = $activityTitles[$aid] ?? null;

                $msg = "Anda memenuhi syarat klaim badge (badge 3). Pada aktivitas " .
                    ($title ? "\"{$title}\" (ID {$aid})" : "ID {$aid}") .
                    " status_benar tercatat " . ($r->status_benar ? 'TRUE' : 'FALSE') . " (ID result: {$r->id}).";

                return [
                    'eligible' => true,
                    'reason' => $msg,
                    'data' => [
                        'class_id' => $classId,
                        'activity_id' => $aid,
                        'activity_title' => $title,
                        'result_id' => $r->id,
                        'total_benar' => $r->total_benar,
                        'status_benar' => (bool) $r->status_benar,
                        'updated_at' => $r->updated_at,
                    ]
                ];
            }

            // tidak ditemukan di semua kelas
            return [
                'eligible' => false,
                'reason' => 'Anda belum menyelesaikan aktivitas dengan status benar (status_benar = true) pada aktivitas manapun di kelas Anda.'
            ];
        } catch (\Exception $e) {
            \Log::error('checkBadgeEligibilityForFullCorrectPerActivity error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'eligible' => false,
                'reason' => 'Terjadi kesalahan saat memeriksa syarat klaim. Silakan coba lagi atau hubungi admin.'
            ];
        }
    }



}
