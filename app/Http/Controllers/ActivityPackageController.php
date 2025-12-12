<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ActivityPackageController extends Controller
{
    // list packages (for klaim page) - can be filtered by subject/class
    public function index(Request $req)
    {
        $query = DB::table('activity_packages as ap')
            ->leftJoin('activities as a', 'a.id', 'ap.id_activity')
            ->leftJoin('classes as c', 'c.id', 'ap.id_class')
            ->select('ap.*', 'a.title as activity_title', 'c.name as class_name')
            ->orderBy('ap.created_at', 'desc');

        if ($req->filled('subject_id')) {
            // optional: filter by subject via meta -> we stored filename JSON; easier if activity has topic->subject already in DB
            // skip complex filter for now
        }

        $rows = $query->paginate(20);
        return response()->json($rows); // used by AJAX listing
    }

    // create package from an activity
    public function store(Request $req, $id)
    {
        $activity = DB::table('activities')->where('id', $id)->first();
        if (!$activity)
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);

        $qids = DB::table('activity_question')->where('id_activity', $id)->pluck('id_question')->toArray();
        $questions = DB::table('question')->whereIn('id', $qids)->get();

        $topic = DB::table('topics')->where('id', $activity->id_topic)->first();
        $subject = $topic ? DB::table('subject')->where('id', $topic->id_subject)->first() : null;
        $class = $subject ? DB::table('classes')->where('id', $subject->id_class)->first() : null;

        $payload = [
            'meta' => [
                'activity' => (array) $activity,
                'topic' => $topic ? (array) $topic : null,
                'subject' => $subject ? (array) $subject : null,
                'class' => $class ? (array) $class : null,
                'exported_by' => Auth::id(),
                'exported_at' => now()->toDateTimeString(),
            ],
            'questions' => $questions->map(function ($q) {
                $q = (array) $q;
                if (is_string($q['question']))
                    $q['question'] = json_decode($q['question'], true);
                if (!empty($q['MC_option']) && is_string($q['MC_option']))
                    $q['MC_option'] = json_decode($q['MC_option'], true);
                if (!empty($q['SA_answer']) && is_string($q['SA_answer']))
                    $q['SA_answer'] = json_decode($q['SA_answer'], true);
                return $q;
            })->toArray()
        ];

        $filename = 'package_activity_' . $id . '_' . Str::slug($activity->title ?? 'activity') . '_' . time() . '.json';
        $path = 'activity_packages/' . $filename;
        Storage::disk('local')->put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


        $pkgId = DB::table('activity_packages')->insertGetId([
            'id_activity' => $id,
            'created_by' => Auth::id(),
            'id_class' => $activity->class_id ?? null,
            'title' => $req->input('title') ?? ('Paket: ' . ($activity->title ?? 'Activity')),
            'filename' => $path,
            'notes' => $req->input('notes') ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $pkgId, 'download_url' => route('activity.package.download', $pkgId)]);
    }

    // download raw package file
    public function download($id)
    {
        $pkg = DB::table('activity_packages')->where('id', $id)->first();
        if (!$pkg)
            abort(404);
        if (!Storage::disk('local')->exists($pkg->filename))
            abort(404);
        return response()->download(storage_path('app/' . $pkg->filename), basename($pkg->filename));
    }

    // claim package into a target class
    public function claim(Request $req, $id)
    {
        $pkg = DB::table('activity_packages')->where('id', $id)->first();
        if (!$pkg) {
            return response()->json(['success' => false, 'message' => 'Package not found'], 404);
        }

        if (!Storage::disk('local')->exists($pkg->filename)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        $json = json_decode(Storage::disk('local')->get($pkg->filename), true);
        if (!$json || !isset($json['meta']['activity'])) {
            return response()->json(['success' => false, 'message' => 'Invalid package'], 400);
        }

        $targetClassId = $req->input('target_class_id');
        if (!$targetClassId) {
            return response()->json(['success' => false, 'message' => 'Target class required'], 422);
        }

        // verifikasi kelas target ada di tabel classes
        $targetClass = DB::table('classes')->where('id', $targetClassId)->first();
        if (!$targetClass) {
            return response()->json(['success' => false, 'message' => 'Target class not found'], 404);
        }

        $duplicate = $req->boolean('duplicate', false);

        DB::beginTransaction();
        try {
            $act = $json['meta']['activity'];

            //
            // 0) COPY SUBJECT: jangan update subject lama, tapi coba reuse by (name + id_class target),
            //    jika tidak ada -> create new subject with id_class = targetClass
            //    Result: $finalSubjectId
            //
            $finalSubjectId = null;
            if (isset($json['meta']['subject'])) {
                $metaSub = $json['meta']['subject'];

                // Prefer reuse: find subject with same name under target class
                if (!empty($metaSub['name'])) {
                    $foundSub = DB::table('subject')
                        ->where('name', $metaSub['name'])
                        ->where('id_class', $targetClass->id)
                        ->first();

                    if ($foundSub) {
                        $finalSubjectId = $foundSub->id;
                    }
                }

                // If not found, create new subject in target class (copy metadata where sensible)
                if (!$finalSubjectId) {
                    $newSubId = DB::table('subject')->insertGetId([
                        'name' => $metaSub['name'] ?? ('Subject for ' . ($json['meta']['activity']['title'] ?? 'Imported')),
                        'id_class' => $targetClass->id,
                        'created_by' => $metaSub['created_by'] ?? Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $finalSubjectId = $newSubId;
                }

                // sync in-memory meta (optional)
                $json['meta']['subject']['id'] = $finalSubjectId;
                $json['meta']['subject']['id_class'] = $targetClass->id;
            }

            //
            // 1) COPY TOPIC: jangan update topic lama. Try reuse by (title + finalSubjectId),
            //    if not found -> create new topic under finalSubjectId
            //    Result: $finalTopicId
            //
            $finalTopicId = $req->input('topic_id') ?? null;
            if (isset($json['meta']['topic'])) {
                $metaTopic = $json['meta']['topic'];

                // try reuse: find topic by title under the subject we will use
                if (!empty($metaTopic['title']) && $finalSubjectId) {
                    $foundTopic = DB::table('topics')
                        ->where('title', $metaTopic['title'])
                        ->where('id_subject', $finalSubjectId)
                        ->first();

                    if ($foundTopic) {
                        $finalTopicId = $foundTopic->id;
                    }
                }

                // if still null -> create new topic under finalSubjectId (copy metadata)
                if (!$finalTopicId) {
                    $newTopicId = DB::table('topics')->insertGetId([
                        'title' => $metaTopic['title'] ?? ('Topic for ' . ($json['meta']['activity']['title'] ?? 'Imported')),
                        'description' => $metaTopic['description'] ?? null,
                        'id_subject' => $finalSubjectId ?? null,
                        'created_by' => $metaTopic['created_by'] ?? Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $finalTopicId = $newTopicId;
                }

                // sync in-memory meta (optional)
                $json['meta']['topic']['id'] = $finalTopicId;
                $json['meta']['topic']['id_subject'] = $finalSubjectId;
            }

            // keep request-provided topic_id if still no finalTopicId
            if (!$finalTopicId && $req->filled('topic_id')) {
                $finalTopicId = $req->input('topic_id');
            }

            //
            // 2) create activity -> set id_topic = $finalTopicId so activity is tied to copied topic/subject/class relation
            //
            $activityPayload = [
                'title' => $req->input('title') ?? ($pkg->title ?? ($act['title'] ?? 'Imported Activity')),
                'addaptive' => $act['addaptive'] ?? 'no',
                'status' => $act['status'] ?? 'basic',
                'type' => $act['type'] ?? 'quiz',
                'durasi_pengerjaan' => $act['durasi_pengerjaan'] ?? null,
                'deadline' => $act['deadline'] ?? null,
                'jumlah_soal' => $act['jumlah_soal'] ?? null,
                'id_topic' => $finalTopicId ?? ($req->input('topic_id') ?? ($act['id_topic'] ?? null)),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $newActivityId = DB::table('activities')->insertGetId($activityPayload);

            //
            // 3) proses soal: link atau duplicate; jika insert soal baru -> gunakan id_topic = $finalTopicId
            //
            $questions = $json['questions'] ?? [];
            foreach ($questions as $q) {
                $useQuestionId = null;

                if (!$duplicate && isset($q['id'])) {
                    $exists = DB::table('question')->where('id', $q['id'])->first();
                    if ($exists) {
                        $useQuestionId = $exists->id;
                    }
                }

                if (!$useQuestionId) {
                    $qText = is_array($q['question']) ? ($q['question']['text'] ?? null) : null;
                    if ($qText) {
                        $found = DB::table('question')
                            ->where('type', $q['type'])
                            ->where('question', 'like', '%' . substr($qText, 0, 40) . '%')
                            ->first();
                        if ($found) {
                            $useQuestionId = $found->id;
                        }
                    }
                }

                if (!$useQuestionId) {
                    $insert = [
                        'type' => $q['type'] ?? 'MultipleChoice',
                        'question' => json_encode($q['question'] ?? []),
                        'MC_option' => isset($q['MC_option']) ? json_encode($q['MC_option']) : null,
                        'SA_answer' => isset($q['SA_answer']) ? json_encode($q['SA_answer']) : null,
                        'MC_answer' => $q['MC_answer'] ?? null,
                        'difficulty' => $q['difficulty'] ?? 'mudah',
                        'id_topic' => $finalTopicId ?? ($req->input('topic_id') ?? ($q['id_topic'] ?? null)),
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $useQuestionId = DB::table('question')->insertGetId($insert);
                }

                DB::table('activity_question')->insert([
                    'id_activity' => $newActivityId,
                    'id_question' => $useQuestionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // intentionally: do NOT rewrite JSON file, do NOT update activity_packages.id_class, do NOT update original subject/topic rows

            DB::commit();

            return response()->json([
                'success' => true,
                'new_activity_id' => $newActivityId,
                'assigned_class_id' => $targetClass->id,
                'subject_id' => $finalSubjectId,
                'topic_id' => $finalTopicId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }



}
