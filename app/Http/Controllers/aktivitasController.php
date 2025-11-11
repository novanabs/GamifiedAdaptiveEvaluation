<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityResult;
use App\Models\nilai;
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
        $activity = Activity::with('questions')->findOrFail($id);
        $judul = $activity->title;
        $topik = $activity->status;
        $konten = json_encode($activity->questions);
        $id_activity = $activity->id; 

        return view('siswa.menjawabSoal', compact('judul', 'topik', 'konten', 'id_activity'));
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

        return redirect()->route('dashboard.siswa')->with('success', 'Nilai berhasil disimpan!');
    }

}
