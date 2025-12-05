<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class siswaController extends Controller
{
    public function dashboardSiswa()
    {
        $user = Auth::user();

        // 🔹 Ambil semua badge siswa (jika ingin menampilkan banyak)
        $userBadges = DB::table('user_badge')
            ->join('badge', 'user_badge.id_badge', '=', 'badge.id')
            ->where('user_badge.id_student', $user->id)
            ->select(
                'badge.id',
                'badge.name',
                'badge.description',
                'badge.path_icon'
            )
            ->get();

        // 🔹 Ambil data kelas siswa login
        $kelasList = DB::table('student_classes')
            ->join('classes', 'student_classes.id_class', '=', 'classes.id')
            ->where('student_classes.id_student', $user->id)
            ->select('classes.id', 'classes.name', 'classes.level', 'classes.token')
            ->get();

        // -----------------------------
        // Aktivitas: KUMPULKAN PER KELAS
        // -----------------------------
        $activitiesByClass = collect();

        foreach ($kelasList as $kelas) {
            $raw = DB::table('activities')
                ->join('topics', 'activities.id_topic', '=', 'topics.id')
                ->join('subject', 'topics.id_subject', '=', 'subject.id')
                ->join('classes', 'subject.id_class', '=', 'classes.id')
                ->where('classes.id', $kelas->id)
                ->leftJoin('activity_result', function ($join) use ($user) {
                    $join->on('activities.id', '=', 'activity_result.id_activity')
                        ->where('activity_result.id_user', '=', $user->id);
                })
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

            $grouped = $raw->groupBy('id_topic')->map(function ($group) {
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

            $activitiesByClass->push((object) [
                'class_id' => $kelas->id,
                'class_name' => $kelas->name,
                'activities' => $grouped
            ]);
        }

        // -----------------------------
        // Statistik global
        // -----------------------------
        $rawActivitiesAll = DB::table('activities')
            ->join('topics', 'activities.id_topic', '=', 'topics.id')
            ->join('subject', 'topics.id_subject', '=', 'subject.id')
            ->join('classes', 'subject.id_class', '=', 'classes.id')
            ->whereIn('classes.id', $kelasList->pluck('id'))
            ->leftJoin('activity_result', function ($join) use ($user) {
                $join->on('activities.id', '=', 'activity_result.id_activity')
                    ->where('activity_result.id_user', '=', $user->id);
            })
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
            ->get();

        $jumlahAktivitas = $rawActivitiesAll->count();
        $jumlahRemedial = $rawActivitiesAll->where('result_status', 'Remedial')->count();

        // -----------------------------
        // Leaderboard per kelas (LOGIC BENAR)
        // -----------------------------
        $leaderboardsPerClass = [];

        foreach ($kelasList as $kelas) {

            // Ambil siswa dalam kelas
            $students = DB::table('student_classes')
                ->where('id_class', $kelas->id)
                ->pluck('id_student');

            if ($students->isEmpty()) {
                $leaderboardsPerClass[] = (object) [
                    'class_id' => $kelas->id,
                    'class_name' => $kelas->name,
                    'students' => []
                ];
                continue;
            }

            // Ambil subject yang dimiliki kelas
            $subjectIds = DB::table('subject')
                ->where('id_class', $kelas->id)
                ->pluck('id');

            if ($subjectIds->isEmpty())
                continue;

            // Ambil topics milik subject tersebut
            $topicIds = DB::table('topics')
                ->whereIn('id_subject', $subjectIds)
                ->pluck('id');

            if ($topicIds->isEmpty())
                continue;

            // Ambil aktivitas milik topics tersebut
            $activityIds = DB::table('activities')
                ->whereIn('id_topic', $topicIds)
                ->pluck('id');

            if ($activityIds->isEmpty())
                continue;

            // Cari result siswa *khusus aktivitas kelas ini*
            $lb = DB::table('activity_result')
                ->join('users', 'activity_result.id_user', '=', 'users.id')
                ->whereIn('activity_result.id_user', $students)
                ->whereIn('activity_result.id_activity', $activityIds)
                ->select(
                    'users.id',
                    'users.name',
                    DB::raw('SUM(activity_result.result) as total_score')
                )
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_score')
                ->get();

            // 6️⃣ Format output
            $leaderboardsPerClass[] = (object) [
                'class_id' => $kelas->id,
                'class_name' => $kelas->name,
                'students' => $lb->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'name' => $row->name,
                        'total_score' => (float) $row->total_score
                    ];
                })->toArray()
            ];
        }

        // -----------------------------
        // View
        // -----------------------------
        return view('siswa.dashboardsiswa', [
            'user' => $user,
            'userBadges' => $userBadges,
            'kelasList' => $kelasList,
            'activitiesByClass' => $activitiesByClass,
            'jumlahAktivitas' => $jumlahAktivitas,
            'jumlahRemedial' => $jumlahRemedial,
            'leaderboardsPerClass' => $leaderboardsPerClass
        ]);
    }

}
