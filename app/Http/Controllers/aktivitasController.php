<?php

namespace App\Http\Controllers;

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

        // 🔹 Ambil semua aktivitas siswa berdasarkan kelasnya (tanpa groupBy)
        $activities = DB::table('activities')
            ->join('topics', 'activities.id_topic', '=', 'topics.id')
            ->join('subject', 'topics.id_subject', '=', 'subject.id')
            ->join('classes', 'subject.id_class', '=', 'classes.id')
            ->join('student_classes', 'classes.id', '=', 'student_classes.id_class')
            ->join('users', 'student_classes.id_student', '=', 'users.id')
            ->where('users.id', $user->id)
            ->whereIn('classes.token', $kelasList->pluck('token'))
            ->select(
                'activities.id as id_aktivitas',
                'activities.title as aktivitas',
                'activities.status',
                'activities.result',
                'activities.result_status',
                'topics.title as topik',
                'subject.name as mapel',
                'activities.created_at'
            )
            ->orderBy('activities.created_at', 'desc')
            ->get();

        // 🔹 Hitung statistik
        $jumlahAktivitas = $activities->count();
        $jumlahRemedial = $activities->where('result_status', 'Remedial')->count();

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
}
