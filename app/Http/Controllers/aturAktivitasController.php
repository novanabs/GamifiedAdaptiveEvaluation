<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class aturAktivitasController extends Controller
{
    public function halamanAturSoal($idAktivitas, Request $request)
    {
        $aktivitas = DB::table('activities')->where('id', $idAktivitas)->first();

        // 🔥 Ambil id_topic dari URL
        $idTopic = $request->query('topic');

        // 🔥 Filter soal: dibuat guru ini + sesuai topik
        $questions = DB::table('question')
            ->where('created_by', Auth::id())
            ->where('id_topic', $idTopic)
            ->get();

        $selectedIds = DB::table('activity_question')
            ->where('id_activity', $idAktivitas)
            ->pluck('id_question')
            ->toArray();

        $selectedQuestions = Question::whereIn('id', $selectedIds)->get();

        return view('guru.atursoal', compact(
            'aktivitas',
            'questions',
            'selectedIds',
            'selectedQuestions'
        ));
    }


    public function ambilSoalAjax(Request $request, $idAktivitas)
    {
        $request->validate([
            'jumlah' => 'required|numeric|min:1'
        ]);

        // Ambil aktivitas beserta topiknya
        $aktivitas = Activity::findOrFail($idAktivitas);
        $isAdaptive = ($aktivitas->addaptive === 'yes');

        // 🔥 Filter soal BENAR: guru yg membuat + topik yg sama
        $baseQuery = Question::where('created_by', Auth::id())
            ->where('id_topic', $aktivitas->id_topic);

        $n = intval($request->jumlah);

        if ($isAdaptive) {

            $easyCount = max(0, $n - 2);
            $hardCount = max(0, $n - 2);
            $mediumCount = max(0, $n);

            // Ambil soal berdasarkan kesulitan
            $easyPool = (clone $baseQuery)->where('difficulty', 'mudah')
                ->inRandomOrder()->take($easyCount)->get();

            $mediumPool = (clone $baseQuery)->where('difficulty', 'sedang')
                ->inRandomOrder()->take($mediumCount)->get();

            $hardPool = (clone $baseQuery)->where('difficulty', 'sulit')
                ->inRandomOrder()->take($hardCount)->get();

            $final = $easyPool->merge($mediumPool)->merge($hardPool);

            return response()->json([
                'adaptive' => true,
                'easy' => $easyCount,
                'medium' => $mediumCount,
                'hard' => $hardCount,
                'total' => $final->count(),
                'data' => $final->map(fn($q) => [
                    'id' => $q->id,
                    'difficulty' => $q->difficulty,
                    'type' => $q->type,
                    'text' => json_decode($q->question)->text ?? '-'
                ])
            ]);
        }

        $final = $baseQuery
            ->inRandomOrder()
            ->take($n)
            ->get();

        return response()->json([
            'adaptive' => false,
            'total' => $final->count(),
            'data' => $final->map(fn($q) => [
                'id' => $q->id,
                'difficulty' => $q->difficulty,
                'type' => $q->type,
                'text' => json_decode($q->question)->text ?? '-'
            ])
        ]);
    }

    public function simpanAturSoal(Request $request, $idAktivitas)
    {
        $request->validate([
            'id_question' => 'nullable|array'
        ]);

        $ids = $request->input('id_question', []);

        DB::beginTransaction();
        try {
            // Hapus dulu yang lama untuk aktivitas ini
            DB::table('activity_question')->where('id_activity', $idAktivitas)->delete();

            // Simpan yang baru (jika ada)
            if (!empty($ids)) {
                $insert = [];
                $now = now();
                foreach ($ids as $qid) {
                    $insert[] = [
                        'id_activity' => $idAktivitas,
                        'id_question' => $qid,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('activity_question')->insert($insert);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('simpanAturSoal error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function tambahSoalManual(Request $req, $idAktivitas)
    {
        DB::table('activity_question')->insert([
            'id_activity' => $idAktivitas,
            'id_question' => $req->id_question,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function hapusSoalManual(Request $req, $idAktivitas)
    {
        DB::table('activity_question')
            ->where('id_activity', $idAktivitas)
            ->where('id_question', $req->id_question)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function getQuestion($id)
    {
        $q = Question::find($id);

        $qData = json_decode($q->question);

        return response()->json([
            'id' => $q->id,
            'difficulty' => $q->difficulty,
            'type' => $q->type,
            'text' => $qData->text ?? '-',
        ]);
    }
    public function clearAll($id)
    {
        DB::table('activity_question')
            ->where('id_activity', $id)
            ->delete();

        return response()->json(['success' => true]);
    }




}
