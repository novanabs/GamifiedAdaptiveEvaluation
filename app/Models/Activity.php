<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';
    protected $fillable = [
        'title',
        'status',
        'type',
        'deadline',
        'id_topic',
        'addaptive',
        'durasi_pengerjaan'
    ];

    // (8) belongsTo Topic
    public function topic()
    {
        return $this->belongsTo(Topic::class, 'id_topic');
    }

    // (9) manyToMany with Question
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'activity_question', 'id_activity', 'id_question');
    }

    public function nilai()
    {
        return $this->hasOne(ActivityResult::class, 'id_activity');
    }
}
